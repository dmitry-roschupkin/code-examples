SELECT
    CONCAT(TABLE_SCHEMA, '.', TABLE_NAME)
FROM information_schema.tables
WHERE TABLE_SCHEMA REGEXP '^(CALC|GENERAL)$'
  AND TABLE_NAME NOT REGEXP '^(acc_email_log|acc_logs|acc_wh_log|site_user_sessions|site_price_queries|wrong_codes|producer_wrong_links|wrong_codes_rule_conditions)$'