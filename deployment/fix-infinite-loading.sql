-- Run in phpMyAdmin AFTER selecting your WordPress database.
-- Fixes infinite loading caused by wrong site URL / SSL mismatch.

UPDATE wp_options SET option_value = 'https://accepted.phoenixtechs.tech' WHERE option_name = 'siteurl';
UPDATE wp_options SET option_value = 'https://accepted.phoenixtechs.tech' WHERE option_name = 'home';
