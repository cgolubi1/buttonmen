CREATE TABLE player_status (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL
);

INSERT INTO player_status (name)
VALUES
    ('ACTIVE'),
    ('UNVERIFIED'),
    ('DISABLED');

ALTER TABLE player
    ADD COLUMN status_id TINYINT UNSIGNED NULL AFTER status;

UPDATE player p
SET p.status_id = (SELECT ps.id FROM player_status ps WHERE ps.name = p.status);

ALTER TABLE player
    DROP COLUMN status,
    CHANGE status_id status_id TINYINT(3) UNSIGNED NOT NULL,
    ADD FOREIGN KEY (status_id) REFERENCES player_status(id);

DROP VIEW IF EXISTS player_view;
CREATE VIEW player_view
AS SELECT
    a.auth_key,
    i.id,
    i.name_ingame,
    i.password_hashed,
    i.name_irl,
    i.email,
    i.is_email_public,
    ps.name AS status,
    i.dob_month,
    i.dob_day,
    i.gender,
    i.autopass,
    i.monitor_redirects_to_game,
    i.monitor_redirects_to_forum,
    i.automatically_monitor,
    i.image_path,
    i.image_size,
    i.uses_gravatar,
    i.comment,
    i.homepage,
    i.favorite_button_id,
    i.favorite_buttonset_id,
    i.player_color,
    i.opponent_color,
    i.neutral_color_a,
    i.neutral_color_b,
    i.last_action_time,
    i.last_access_time,
    i.creation_time,
    i.fanatic_button_id,
    i.n_games_won,
    i.n_games_lost
FROM player AS i
    LEFT JOIN player_auth AS a ON i.id = a.id
    LEFT JOIN player_status AS ps ON ps.id = i.status_id;
