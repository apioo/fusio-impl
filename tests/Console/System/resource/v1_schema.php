<?php
/**
 * @var \Doctrine\DBAL\Schema\Schema $schema
 */

$fooTable = $schema->createTable('acme_foo');
$fooTable->addColumn('id', 'integer', array('autoincrement' => true));
$fooTable->addColumn('title', 'string', array('length' => 64));
$fooTable->addColumn('content', 'text');
$fooTable->addColumn('insertDate', 'datetime');
$fooTable->setPrimaryKey(array('id'));

$barTable = $schema->createTable('acme_bar');
$barTable->addColumn('id', 'integer', array('autoincrement' => true));
$barTable->addColumn('title', 'string', array('length' => 64));
$barTable->addColumn('content', 'text');
$barTable->addColumn('insertDate', 'datetime');
$barTable->setPrimaryKey(array('id'));
