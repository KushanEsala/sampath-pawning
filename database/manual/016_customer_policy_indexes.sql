-- Manual MySQL schema preparation for customer-wide pawn limits.
-- Run once in the target database before deploying customer-policy code.
-- Do not run Laravel migrations. This script adds indexes only; it changes no data.
-- Before running, verify the index names are not already present with SHOW INDEX.

CREATE INDEX idx_customers_nic ON customers (NIC);
CREATE INDEX idx_pawn_customer_active ON t_pawn_sums (Customer_NIC, IsRedeemed, isForfeit);
CREATE INDEX idx_opening_customer_active ON t_opening_pawn_sums (Customer_NIC, IsRedeemed, isForfeit);
