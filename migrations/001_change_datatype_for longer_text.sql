-- Change text columns to support larger content in task
ALTER TABLE task
MODIFY user_desc MEDIUMTEXT NOT NULL,
MODIFY solution MEDIUMTEXT;

-- Change text columns to support larger content in email
ALTER TABLE email
MODIFY user_desc MEDIUMTEXT NOT NULL,
MODIFY solution MEDIUMTEXT NOT NULL;

-- Change text columns to support larger content in email
ALTER TABLE comment_history
MODIFY comment MEDIUMTEXT DEFAULT NULL,