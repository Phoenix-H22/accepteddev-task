-- Run AFTER importing accepteddevtask-database-export.sql into FastPanel
UPDATE wp_options SET option_value = 'https://accepted.phoenixtechs.tech' WHERE option_name = 'siteurl';
UPDATE wp_options SET option_value = 'https://accepted.phoenixtechs.tech' WHERE option_name = 'home';
