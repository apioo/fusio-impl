
-- acme_foo
CREATE TABLE acme_foo (
  id integer PRIMARY KEY,
  title char(64) NOT NULL,
  content text NOT NULL,
  insertDate datetime NOT NULL
);

-- acme_bar
CREATE TABLE acme_bar (
  id integer PRIMARY KEY,
  title char(64) NOT NULL,
  content text NOT NULL,
  insertDate datetime NOT NULL
)
