# DataCooker


## What is it ?

It's library to different kinds of data store. 

This library provide to interface by abstracting access data store (\pdo, \Redis, \Memcached and so on) 

Supported DataStore

Relation Database : Pdo-managed database  

KeyValue Store : Memcached, redis (It's on development) 

Read this in other languages : [한국어](README.ko.md)

## How to use

data base schema

```
create table Item
(
	id1 int auto_increment,
	id2 int not null,
	id3 int not null,
	attr1 int not null,
	attr2 int not null,
	attr3 int not null,
	constraint Item_pk
		primary key (id1)
);

```

class defined 

```
final class Item
{
    /**
     * @dataCookerAutoIncrement
     * @dataCookerIdentifier
     */
    public $id1;
    
    /**
     * @dataCookerIdentifier
     */
    public $id2;
    
    /**
     * @dataCookerIdentifier
     */
    public $id3;
    
    /**
     * @dataCookerAttribute
     */
    public $attr1;
    
    /**
     * @dataCookerAttribute
     */
    public $attr2;
    
    /**
     * @dataCookerAttribute
     */
    public $attr3;
}
```

you can use as bellows.

```php

$store = new Buffer(new RelationDatabase(null, new Database('localhost', 3306, 'dbName, new Auth('id', 'password'))));

```

other complex example

```php

$store = new Buffer(
            new Memcached(
                new RelationDatabase(null, new Database('localhost', 3306, 'dbName, new Auth('id', 'password')))
                , array(new \battlecook\Config\Memcache('localhost'))));
```


## License

DataCooker is licensed under MIT