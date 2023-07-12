create sequence user_types_id_seq;

alter sequence user_types_id_seq owner to hwsys_admin;

create sequence homework_id_seq;

alter sequence homework_id_seq owner to hwsys_admin;

create table user_types
(
    type_id       integer generated always as identity
        primary key,
    name          varchar(20) not null
        unique,
    readable_name varchar(30) not null
);

alter table user_types
owner to hwsys_admin;

alter sequence user_types_id_seq owned by user_types.type_id;

create table users
(
    id                integer                             not null
        primary key,
    registration_date timestamp default CURRENT_TIMESTAMP not null,
    first_name        varchar(30)                         not null,
    last_name         varchar(30)                         not null,
    middle_name       varchar(30),
    password          varchar(255)                        not null,
    type              smallint                            not null
        references user_types,
    ip                varchar(45),
    is_confirmed      boolean   default false             not null
);

alter table users
owner to hwsys_admin;

create table courses
(
    id               integer generated always as identity
        primary key,
    title            varchar(255)                        not null,
    description      text,
    start_date       date,
    end_date         date,
    difficulty_level varchar(15),
    category         varchar(50),
    availability     boolean   default false             not null,
    created_at       timestamp default CURRENT_TIMESTAMP not null,
    updated_at       timestamp default CURRENT_TIMESTAMP not null,
    updated_by       integer                             not null
        references users
            on delete cascade,
    constraint check_end_date
        check (end_date >= start_date)
);

alter table courses
owner to hwsys_admin;

create table user_courses
(
    user_id      integer not null
        references users
            on delete cascade,
    course_id    integer not null
        references courses
            on delete cascade,
    is_confirmed boolean,
    primary key (user_id, course_id)
);

alter table user_courses
owner to hwsys_admin;

create table homeworks
(
    id            integer generated always as identity
        constraint homework_pkey
            primary key,
    title         varchar(255)                        not null,
    description   text,
    max_attempts  smallint
        constraint check_max_attempts
            check (max_attempts >= 0),
    total_marks   smallint
        constraint check_total_marks
            check (total_marks >= 0),
    passing_marks smallint
        constraint check_passing_marks
            check (passing_marks >= 0),
    start_date    date,
    end_date      date,
    created_at    timestamp default CURRENT_TIMESTAMP not null,
    updated_at    timestamp default CURRENT_TIMESTAMP not null,
    created_by    integer                             not null
        constraint homework_created_by_fkey
            references users
            on delete cascade,
    updated_by    integer
        constraint homework_updated_by_fkey
            references users
            on delete cascade,
    constraint check_end_date
        check (end_date >= start_date)
);

alter table homeworks
owner to hwsys_admin;

alter sequence homework_id_seq owned by homeworks.id;

create table course_homeworks
(
    course_id   integer not null
        references courses
            on delete cascade,
    homework_id integer not null
        references homeworks
            on delete cascade,
    primary key (course_id, homework_id)
);

alter table course_homeworks
owner to hwsys_admin;

create table tasks
(
    id          integer generated always as identity
        primary key,
    type        varchar(30)                         not null,
    title       varchar(255)                        not null,
    description text                                not null,
    options     text[]                              not null,
    answer      text[]                              not null,
    max_score   smallint
        constraint check_positive_max_score
            check (max_score >= 0),
    created_at  timestamp default CURRENT_TIMESTAMP not null,
    updated_at  timestamp                           not null,
    created_by  integer                             not null
        references users
            on delete cascade,
    updated_by  integer                             not null
        references users
            on delete cascade
);

alter table tasks
owner to hwsys_admin;

create table homework_tasks
(
    homework_id integer not null
        references homeworks
            on delete cascade,
    task_id     integer not null
        constraint homework_tasks_tasks_id_fk
            references tasks
            on delete cascade,
    primary key (homework_id, task_id)
);

alter table homework_tasks
owner to hwsys_admin;

create table attempts
(
    id              integer generated always as identity
        primary key,
    score           integer,
    submission_time timestamp default CURRENT_TIMESTAMP
);

alter table attempts
owner to hwsys_admin;

create table user_homework_attempts
(
    user_id     integer not null,
    homework_id integer not null
        references homeworks
            on delete cascade,
    attempt_id  integer not null
        references attempts
            on delete cascade
        constraint user_homework_attempts_user_id_fkey
            references attempts
            on delete cascade,
    primary key (user_id, homework_id, attempt_id)
);

alter table user_homework_attempts
owner to hwsys_admin;

create table attempt_inputs
(
    attempt_id integer not null
        references attempts
            on delete cascade,
    task_id    integer not null
        references tasks
            on delete cascade,
    user_input text[],
    is_correct boolean,
    primary key (attempt_id, task_id)
);

alter table attempt_inputs
owner to hwsys_admin;

create function calculate_score() returns trigger
    language plpgsql
as
$$
DECLARE
    total_score INT := 0;
BEGIN
    SELECT SUM(t.max_score)
    INTO total_score
    FROM attempt_inputs AS ai
             JOIN tasks AS t ON ai.task_id = t.id
    WHERE ai.attempt_id = NEW.attempt_id
            AND ai.is_correct = TRUE;

    UPDATE attempts
    SET score = total_score
    WHERE id = NEW.attempt_id;
    RETURN NEW;
END;
$$;

alter function calculate_score() owner to hwsys_admin;

create trigger calculate_score_trigger
    after insert or update
    on user_homework_attempts
    for each row
execute procedure calculate_score();

create function check_answer() returns trigger
    language plpgsql
as
$$
DECLARE
    correct_answer text[];
    task_type varchar(30);
BEGIN
    SELECT answer, type INTO correct_answer, task_type
    FROM tasks
    WHERE id = NEW.task_id;

    IF task_type = 'multiple_choice' THEN
        IF NEW.user_input = correct_answer THEN
            NEW.is_correct = TRUE;
        ELSE
            NEW.is_correct = FALSE;
        END IF;
    ELSIF task_type = 'single_choice' OR task_type = 'word_match' THEN
        IF NEW.user_input && correct_answer THEN
            NEW.is_correct = TRUE;
        ELSE
            NEW.is_correct = FALSE;
        END IF;
    END IF;
    RAISE NOTICE 'shit';
    RETURN NEW;
END;
$$;

alter function check_answer() owner to hwsys_admin;

create trigger check_answer_trigger
    before insert
    on attempt_inputs
    for each row
execute procedure check_answer();

