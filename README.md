# DataCooker


## What is it ?

It's project for different kinds of data store. 

It provides an interface by abstracting individual libraries (\ pdo, \ Redis, \ Memcached, etc.) that access data stores.

Supported DataStore

Relation Database : Pdo-managed database  

KeyValue Store : memcached, redis, apcu

Read this in other languages : [한국어](README.ko.md)

## How to install

this project is being prepared to upload at packagist.org

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

```php
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

annotation in class represent data attribute.

There are three attribution to represent.

* @dataCookerIdentifier : required. represent complex unique id (have to declare more than once.)

* @dataCookerAttribute : required. represent attribution (have to declare more than once.)

* @dataCookerAutoIncrement : optional. represent auto increment value.

DataStore provides six interface (get, search, set, add, remove, commit) 


```php
$store = new RelationDatabase(null, new Database('localhost', 3306, 'dbName, new Auth('id', 'password')));
       
$object = new Item();
$object->id1 = 1;
$object->id2 = 1;
$object->id3 = 1;
$object->attr1 = 1;
$object->attr2 = 1;
$object->attr3 = 1;

$ret = $store->get($object);
$ret = $store->search($object): array;
$ret = $store->set($object);
$ret = $store->add($object);
$ret = $store->remove($object);
$ret = $store->commit($data = null);
```

complex DataStore example ( Memcached and RelationDatabase )

##### before #####

```
status in database
  
+-----+-----+-----+-------+-------+-------+
| id1 | id2 | id3 | attr1 | attr2 | attr3 |
+-----+-----+-----+-------+-------+-------+
|  1  |  1  |  1  |   1   |   1   |   1   |
+-----+-----+-----+-------+-------+-------+

status in memcached
```

##### progress #####
```php
$store =  new Memcached(
            new RelationDatabase(null, new Database('localhost', 3306, 'dbName, new Auth('id', 'password')))
             , array(new \battlecook\Config\Memcache('localhost')))
             
$object = new Item();
$object->id1 = 1;
$object->id2 = 1;
$object->id3 = 2;
$object->attr1 = 1;
$object->attr2 = 1;
$object->attr3 = 1;

$ret = $store->add($object);
```

##### after #####
```
status in database
  
+-----+-----+-----+-------+-------+-------+
| id1 | id2 | id3 | attr1 | attr2 | attr3 |
+-----+-----+-----+-------+-------+-------+
|  1  |  1  |  1  |   1   |   1   |   1   |
+-----+-----+-----+-------+-------+-------+
|  1  |  1  |  2  |   1   |   1   |   1   |
+-----+-----+-----+-------+-------+-------+

status in memcached
```

Buffered DataStore : 

BufferedDataStore basically store in Php memory.

If you use multiple DataStore with BufferedDataStore, It is different to operate a little.

When BufferedDataStore operate function (get set add remove), The first time, it get data from another repository and load it into php memory.

After that, it only work in php Memory until called commit() function.

Therefore, if you want to be applied from another DataStore, you must call the commit function.

If you are using a BufferedDataStore and @dataCookerAutoIncrement is defined in your class, 

The add function is performed first to get the autoIncrement value incremented.

If it is not defined, it is postprocessed like any other function.

##### before #####

```
before status in database
  
+-----+-----+-----+-------+-------+-------+
| id1 | id2 | id3 | attr1 | attr2 | attr3 |
+-----+-----+-----+-------+-------+-------+
|  1  |  1  |  1  |   1   |   1   |   1   |
+-----+-----+-----+-------+-------+-------+
```
##### progress1 #####
```php
$store =  new Buffered(new RelationDatabase(null, new Database('localhost', 3306, 'dbName, new Auth('id', 'password'))));
             
$object = new Item();
$object->id1 = 1;
$object->id2 = 1;
$object->id3 = 1;
$object->attr1 = 2;
$object->attr2 = 2;
$object->attr3 = 2;

$ret = $store->set($object);
```
##### after1 #####
```
after status in database
  
+-----+-----+-----+-------+-------+-------+
| id1 | id2 | id3 | attr1 | attr2 | attr3 |
+-----+-----+-----+-------+-------+-------+
|  1  |  1  |  1  |   1   |   1   |   1   |
+-----+-----+-----+-------+-------+-------+
```

##### progress2 #####
```php
$store->commit();
```
##### after2 #####
```
after status in database
  
+-----+-----+-----+-------+-------+-------+
| id1 | id2 | id3 | attr1 | attr2 | attr3 |
+-----+-----+-----+-------+-------+-------+
|  1  |  1  |  1  |   2   |   2   |   2   |
+-----+-----+-----+-------+-------+-------+
```

## License

DataCooker is licensed under MIT