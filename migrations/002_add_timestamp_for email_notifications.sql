-- add timestamps for email notifications while ticket remaining in progress
ALTER Table email
ADD column last_updated_at TIMESTAMP NULL DEFAULT NULL;
ADD column last_notified_at TIMESTAMP NULL DEFAULT NULL;