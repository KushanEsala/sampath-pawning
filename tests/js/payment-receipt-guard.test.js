const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const test = require('node:test');

function fixture() {
    const state = { receipt: false, prefilter: null, listeners: {}, cleared: [], button: { disabled: false } };
    const form = {
        querySelectorAll: () => [state.button],
        addEventListener: (name, fn) => { state.listeners[name] = fn; }
    };
    function jquery(selector) {
        return {
            empty() { state.cleared.push(selector); if (selector === '.dynamic-area') state.receipt = false; return this; },
            append() { return this; }, text() { return this; }
        };
    }
    jquery.ajaxPrefilter = fn => { state.prefilter = fn; };
    const document = {
        readyState: 'complete', querySelector: () => form,
        getElementById: () => state.receipt ? {} : null
    };
    const window = { jQuery: jquery, location: { href: 'http://127.0.0.1:8000/pawning_redeem' } };
    vm.runInNewContext(fs.readFileSync('public/assets/js/payment-receipt-guard.js', 'utf8'), { window, document, URL });
    function request(path, success = () => {}) {
        const options = { url: path, data: 'search_receipt_no=100', success };
        state.prefilter(options);
        return options;
    }
    return { state, request };
}

test('payment starts disabled and becomes available only after an active main lookup', () => {
    const { state, request } = fixture();
    assert.equal(state.button.disabled, true);
    const options = request('/search_part_payment_ticket_ajax', () => { state.receipt = true; });
    assert.match(options.data, /payment_workflow=1/);
    options.success('<input id="r_number">');
    assert.equal(state.button.disabled, false);
});

test('closed lookup clears stale receipt and article/history rows', () => {
    const { state, request } = fixture();
    request('/search_receipt_ajax', () => { state.receipt = true; }).success('<input id="r_number">');
    request('/search_receipt_ajax').success({ status: 'not_found' });
    assert.equal(state.button.disabled, true);
    assert.equal(state.receipt, false);
    assert.ok(state.cleared.includes('#articleDetails, #CustomerDetails'));
});

test('changing search invalidates eligibility and ignores responses to previous input', () => {
    const { state, request } = fixture();
    let staleRendered = false;
    const pending = request('/search_receipt_ajax', () => { staleRendered = true; state.receipt = true; });
    state.listeners.input({ target: { id: 'search_receipt' } });
    pending.success('<input id="r_number">');
    assert.equal(staleRendered, false);
    assert.equal(state.button.disabled, true);
});

test('failed companion lookups clear only their own panels', () => {
    const { state, request } = fixture();
    request('/search_receipt_ajax', () => { state.receipt = true; }).success('<input id="r_number">');
    request('/view_article_details_ajax').success({ status: 'not_found' });
    assert.ok(state.cleared.includes('#articleDetails'));
    assert.equal(state.button.disabled, false);
});

test('reset and unverified submission remain blocked even without button state', () => {
    const { state } = fixture();
    let prevented = false;
    state.listeners.submit({ preventDefault: () => { prevented = true; }, stopImmediatePropagation() {} });
    assert.equal(prevented, true);
    state.receipt = true;
    state.listeners.reset();
    assert.equal(state.receipt, false);
    assert.equal(state.button.disabled, true);
});
