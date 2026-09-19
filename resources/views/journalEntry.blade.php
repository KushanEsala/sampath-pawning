<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>NEXUS AI — Intelligent Chat Interface</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;900&family=Share+Tech+Mono&family=Inter:wght@300;400&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:        #020409;
      --surface:   #080d1a;
      --panel:     #0b1120;
      --border:    #1a2744;
      --glow:      #00d4ff;
      --glow2:     #7b2fff;
      --accent:    #ff3d6b;
      --text:      #cdd9f5;
      --muted:     #4a5b80;
      --user-bg:   #0d1f3c;
      --ai-bg:     #07101f;
      --font-head: 'Orbitron', monospace;
      --font-mono: 'Share Tech Mono', monospace;
      --font-body: 'Inter', sans-serif;
    }

    html, body {
      height: 100%;
      background: var(--bg);
      color: var(--text);
      font-family: var(--font-body);
      overflow: hidden;
    }

    /* ── Animated grid bg ── */
    body::before {
      content: '';
      position: fixed; inset: 0;
      background-image:
        linear-gradient(rgba(0,212,255,.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,212,255,.03) 1px, transparent 1px);
      background-size: 40px 40px;
      z-index: 0;
      animation: gridShift 20s linear infinite;
    }
    @keyframes gridShift { from { background-position: 0 0; } to { background-position: 40px 40px; } }

    body::after {
      content: '';
      position: fixed; inset: 0;
      background: radial-gradient(ellipse 80% 60% at 50% -10%, rgba(123,47,255,.18) 0%, transparent 70%),
                  radial-gradient(ellipse 60% 40% at 100% 80%, rgba(0,212,255,.1) 0%, transparent 60%);
      z-index: 0;
      pointer-events: none;
    }

    /* ── Layout ── */
    #app {
      position: relative; z-index: 1;
      display: grid;
      grid-template-rows: auto 1fr auto;
      height: 100vh;
      max-width: 860px;
      margin: 0 auto;
      padding: 0 12px;
    }

    /* ── Header ── */
    header {
      padding: 18px 0 14px;
      display: flex;
      align-items: center;
      gap: 14px;
      border-bottom: 1px solid var(--border);
      animation: fadeDown .6s ease both;
    }

    .logo-ring {
      width: 42px; height: 42px;
      border-radius: 50%;
      border: 2px solid var(--glow);
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 0 16px rgba(0,212,255,.4), inset 0 0 10px rgba(0,212,255,.1);
      animation: pulse 3s ease-in-out infinite;
      flex-shrink: 0;
    }
    .logo-ring svg { width: 22px; height: 22px; }
    @keyframes pulse {
      0%,100% { box-shadow: 0 0 16px rgba(0,212,255,.4), inset 0 0 10px rgba(0,212,255,.1); }
      50%      { box-shadow: 0 0 28px rgba(0,212,255,.7), inset 0 0 16px rgba(0,212,255,.2); }
    }

    .header-text h1 {
      font-family: var(--font-head);
      font-size: 1.15rem;
      font-weight: 900;
      letter-spacing: 4px;
      background: linear-gradient(90deg, var(--glow), var(--glow2));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .header-text p {
      font-family: var(--font-mono);
      font-size: .65rem;
      color: var(--muted);
      letter-spacing: 2px;
      margin-top: 2px;
    }

    .status-dot {
      margin-left: auto;
      display: flex; align-items: center; gap: 7px;
      font-family: var(--font-mono);
      font-size: .6rem;
      color: #00ff88;
      letter-spacing: 1px;
    }
    .status-dot::before {
      content: '';
      width: 7px; height: 7px;
      border-radius: 50%;
      background: #00ff88;
      box-shadow: 0 0 8px #00ff88;
      animation: blink 2s ease-in-out infinite;
    }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }

    /* ── Chat window ── */
    #chat {
      overflow-y: auto;
      padding: 24px 0 10px;
      display: flex;
      flex-direction: column;
      gap: 18px;
      scroll-behavior: smooth;
    }
    #chat::-webkit-scrollbar { width: 4px; }
    #chat::-webkit-scrollbar-track { background: transparent; }
    #chat::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

    /* ── Messages ── */
    .msg {
      display: flex;
      gap: 12px;
      animation: fadeUp .35s ease both;
    }
    @keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:none; } }
    @keyframes fadeDown { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:none; } }

    .msg.user { flex-direction: row-reverse; }

    .avatar {
      width: 34px; height: 34px;
      border-radius: 50%;
      flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
      font-family: var(--font-head);
      font-size: .6rem;
      font-weight: 600;
      letter-spacing: 1px;
    }
    .msg.ai .avatar {
      background: linear-gradient(135deg, var(--glow2), var(--glow));
      box-shadow: 0 0 14px rgba(0,212,255,.3);
      color: #fff;
    }
    .msg.user .avatar {
      background: linear-gradient(135deg, #1a2744, #0d1f3c);
      border: 1px solid var(--border);
      color: var(--glow);
    }

    .bubble {
      max-width: 72%;
      padding: 13px 17px;
      border-radius: 2px;
      font-size: .88rem;
      line-height: 1.65;
      position: relative;
    }
    .msg.ai .bubble {
      background: var(--ai-bg);
      border: 1px solid var(--border);
      border-left: 2px solid var(--glow);
      color: var(--text);
      box-shadow: 0 4px 24px rgba(0,0,0,.5), inset 0 1px 0 rgba(0,212,255,.05);
    }
    .msg.user .bubble {
      background: var(--user-bg);
      border: 1px solid #1e3060;
      border-right: 2px solid var(--glow2);
      color: #e0eaff;
      text-align: right;
      box-shadow: 0 4px 24px rgba(0,0,0,.4);
    }

    /* ── Typing indicator ── */
    .typing-dots {
      display: flex; gap: 5px; align-items: center;
      padding: 6px 2px;
    }
    .typing-dots span {
      width: 7px; height: 7px;
      border-radius: 50%;
      background: var(--glow);
      animation: dot 1.2s ease-in-out infinite;
      opacity: .3;
    }
    .typing-dots span:nth-child(2) { animation-delay: .2s; background: var(--glow2); }
    .typing-dots span:nth-child(3) { animation-delay: .4s; background: var(--accent); }
    @keyframes dot { 0%,100%{opacity:.3;transform:scale(1)} 50%{opacity:1;transform:scale(1.3)} }

    /* ── Welcome state ── */
    #welcome {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      flex: 1;
      gap: 14px;
      padding: 40px 20px;
      text-align: center;
      animation: fadeUp .7s ease .1s both;
    }
    #welcome h2 {
      font-family: var(--font-head);
      font-size: 1.4rem;
      font-weight: 600;
      letter-spacing: 3px;
      background: linear-gradient(90deg, #fff 30%, var(--glow));
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    #welcome p {
      color: var(--muted);
      font-family: var(--font-mono);
      font-size: .72rem;
      letter-spacing: 1px;
      max-width: 380px;
    }
    .suggestions {
      display: flex; flex-wrap: wrap; gap: 8px;
      justify-content: center;
      margin-top: 10px;
    }
    .suggestion-btn {
      background: transparent;
      border: 1px solid var(--border);
      color: var(--muted);
      padding: 8px 14px;
      font-family: var(--font-mono);
      font-size: .68rem;
      letter-spacing: 1px;
      cursor: pointer;
      transition: all .2s;
      border-radius: 2px;
    }
    .suggestion-btn:hover {
      border-color: var(--glow);
      color: var(--glow);
      box-shadow: 0 0 10px rgba(0,212,255,.15);
    }

    /* ── Input area ── */
    #input-area {
      padding: 14px 0 20px;
      border-top: 1px solid var(--border);
      animation: fadeDown .5s ease .2s both;
      opacity: 0;
      animation-fill-mode: forwards;
    }
    @keyframes fadeDown { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }

    .input-wrap {
      display: flex;
      gap: 10px;
      align-items: flex-end;
      background: var(--panel);
      border: 1px solid var(--border);
      border-radius: 3px;
      padding: 10px 12px;
      transition: border-color .2s, box-shadow .2s;
    }
    .input-wrap:focus-within {
      border-color: rgba(0,212,255,.4);
      box-shadow: 0 0 20px rgba(0,212,255,.08);
    }

    #user-input {
      flex: 1;
      background: transparent;
      border: none;
      outline: none;
      color: var(--text);
      font-family: var(--font-body);
      font-size: .87rem;
      line-height: 1.5;
      resize: none;
      max-height: 120px;
      min-height: 24px;
    }
    #user-input::placeholder { color: var(--muted); }

    #send-btn {
      background: linear-gradient(135deg, var(--glow2), var(--glow));
      border: none;
      border-radius: 2px;
      width: 36px; height: 36px;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer;
      flex-shrink: 0;
      transition: opacity .2s, transform .15s, box-shadow .2s;
      box-shadow: 0 0 14px rgba(0,212,255,.3);
    }
    #send-btn:hover { opacity: .85; transform: scale(1.05); box-shadow: 0 0 22px rgba(0,212,255,.5); }
    #send-btn:active { transform: scale(.95); }
    #send-btn:disabled { opacity: .35; cursor: not-allowed; transform: none; }
    #send-btn svg { width: 16px; height: 16px; }

    .input-footer {
      margin-top: 8px;
      font-family: var(--font-mono);
      font-size: .58rem;
      color: var(--muted);
      letter-spacing: 1px;
      text-align: center;
      opacity: .6;
    }

    /* ── Error ── */
    .error-bubble {
      background: rgba(255,61,107,.08);
      border: 1px solid rgba(255,61,107,.3);
      border-left: 2px solid var(--accent);
      color: #ff8fab;
      font-size: .82rem;
      padding: 10px 14px;
      border-radius: 2px;
      font-family: var(--font-mono);
    }

    /* ── Scanline overlay ── */
    .scanlines {
      position: fixed; inset: 0; z-index: 999;
      background: repeating-linear-gradient(
        0deg,
        transparent,
        transparent 2px,
        rgba(0,0,0,.03) 2px,
        rgba(0,0,0,.03) 4px
      );
      pointer-events: none;
    }
  </style>
</head>
<body>
<div class="scanlines"></div>
<div id="app">
  <header>
    <div class="logo-ring">
      <svg viewBox="0 0 24 24" fill="none" stroke="#00d4ff" stroke-width="2" stroke-linecap="round">
        <polygon points="12 2 22 8.5 22 15.5 12 22 2 15.5 2 8.5"/>
        <circle cx="12" cy="12" r="3"/>
        <line x1="12" y1="2" x2="12" y2="9"/>
        <line x1="12" y1="15" x2="12" y2="22"/>
      </svg>
    </div>
    <div class="header-text">
      <h1>NEXUS AI</h1>
      <p>ADVANCED INTELLIGENCE INTERFACE v2.4</p>
    </div>
    <div class="status-dot">ONLINE</div>
  </header>

  <div id="chat">
    <div id="welcome">
      <h2>INITIALIZE SEQUENCE</h2>
      <p>Neural network active. Ask me anything — I'm powered by Claude AI.</p>
      <div class="suggestions">
        <button class="suggestion-btn" onclick="sendSuggestion(this)">Explain quantum computing</button>
        <button class="suggestion-btn" onclick="sendSuggestion(this)">Write a Python function</button>
        <button class="suggestion-btn" onclick="sendSuggestion(this)">What is dark matter?</button>
        <button class="suggestion-btn" onclick="sendSuggestion(this)">Tell me a sci-fi story</button>
      </div>
    </div>
  </div>

  <div id="input-area">
    <div class="input-wrap">
      <textarea id="user-input" placeholder="Transmit your query…" rows="1"></textarea>
      <button id="send-btn" onclick="sendMessage()" title="Send">
        <svg viewBox="0 0 24 24" fill="white"><path d="M22 2L11 13M22 2L15 22l-4-9-9-4 20-7z"/></svg>
      </button>
    </div>
    <p class="input-footer">[ ENTER to send · SHIFT+ENTER for new line ]</p>
  </div>
</div>

<script>
  const chatEl   = document.getElementById('chat');
  const inputEl  = document.getElementById('user-input');
  const sendBtn  = document.getElementById('send-btn');
  const welcomeEl= document.getElementById('welcome');

  let history = [];
  let isLoading = false;

  // Auto-resize textarea
  inputEl.addEventListener('input', () => {
    inputEl.style.height = 'auto';
    inputEl.style.height = Math.min(inputEl.scrollHeight, 120) + 'px';
  });

  inputEl.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
  });

  function sendSuggestion(btn) {
    inputEl.value = btn.textContent;
    sendMessage();
  }

  function addMessage(role, content, isError = false) {
    if (welcomeEl) welcomeEl.style.display = 'none';

    const msg = document.createElement('div');
    msg.className = `msg ${role}`;

    const avatar = document.createElement('div');
    avatar.className = 'avatar';
    avatar.textContent = role === 'ai' ? 'AI' : 'YOU';

    const bubble = document.createElement('div');
    bubble.className = isError ? 'bubble error-bubble' : 'bubble';
    bubble.innerHTML = formatMessage(content);

    msg.appendChild(avatar);
    msg.appendChild(bubble);
    chatEl.appendChild(msg);
    chatEl.scrollTop = chatEl.scrollHeight;
    return bubble;
  }

  function addTyping() {
    if (welcomeEl) welcomeEl.style.display = 'none';
    const msg = document.createElement('div');
    msg.className = 'msg ai';
    msg.id = 'typing-msg';

    const avatar = document.createElement('div');
    avatar.className = 'avatar';
    avatar.textContent = 'AI';

    const bubble = document.createElement('div');
    bubble.className = 'bubble';
    bubble.innerHTML = `<div class="typing-dots"><span></span><span></span><span></span></div>`;

    msg.appendChild(avatar);
    msg.appendChild(bubble);
    chatEl.appendChild(msg);
    chatEl.scrollTop = chatEl.scrollHeight;
    return msg;
  }

  function removeTyping() {
    const el = document.getElementById('typing-msg');
    if (el) el.remove();
  }

  function formatMessage(text) {
    return text
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
      .replace(/\*\*(.*?)\*\*/g,'<strong>$1</strong>')
      .replace(/`([^`]+)`/g,'<code style="background:rgba(0,212,255,.08);padding:1px 5px;border-radius:2px;font-family:var(--font-mono);font-size:.82em;color:#00d4ff">$1</code>')
      .replace(/\n/g,'<br>');
  }

  async function sendMessage() {
    const text = inputEl.value.trim();
    if (!text || isLoading) return;

    isLoading = true;
    sendBtn.disabled = true;
    inputEl.value = '';
    inputEl.style.height = 'auto';

    addMessage('user', text);
    history.push({ role: 'user', content: text });

    const typingEl = addTyping();

    try {
      const response = await fetch('https://api.anthropic.com/v1/messages', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          model: 'claude-sonnet-4-20250514',
          max_tokens: 1000,
          system: 'You are NEXUS, a highly intelligent AI assistant with a slightly futuristic, precise tone. Be helpful, concise, and occasionally reference your advanced capabilities.',
          messages: history
        })
      });

      removeTyping();

      if (!response.ok) {
        const err = await response.json().catch(() => ({}));
        throw new Error(err?.error?.message || `HTTP ${response.status}`);
      }

      const data = await response.json();
      const reply = data.content?.find(b => b.type === 'text')?.text || 'No response received.';

      history.push({ role: 'assistant', content: reply });
      addMessage('ai', reply);

    } catch (err) {
      removeTyping();
      addMessage('ai', `SYSTEM ERROR: ${err.message}`, true);
    } finally {
      isLoading = false;
      sendBtn.disabled = false;
      inputEl.focus();
    }
  }
</script>
</body>
</html>