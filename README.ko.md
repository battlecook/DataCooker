# DataCooker


## What is it ?

DataCooker 는 다양한 저장소에 접근하는 방법을 추상화해서 몇개의 인터페이스로 제공하는 라이브러리 입니다. 

데이타 저장소들에 접근하는 개별 라이브러리들 (\pdo, \Redis, \Memcached 등)을 추상화 하여 인터페이스를 제공합니다. 

현재 제공중인 데이타 저장소 목록은 다음과 같습니다.

관계형 데이타베이스 : Pdo 라이브러리에서 관리하는 데이타베이스들 

키 벨류 저장소 : memcached, redis, apcu

파일 : [PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet) 라이브러리 에서 제공하는 파일 포맷

README 지원언어 : [English](README.md)

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

클레스의 어노테이션으로 데이터의 속성을 구분합니다.

어노테이션으로 표현 할 수 있는 속성엔 다음의 3가지가 있습니다.

* @dataCookerIdentifier : 데이터의 필수 항목입니다.

* @dataCookerAttribute : 데이터의 필수 항목입니다.

* @dataCookerAutoIncrement : 데이터의 선택 항목입니다. 

DataStore 에는 5가지 인터페이스(get, set, add, remove, commit)를 제공합니다. 

@dataCookerAutoIncrement 의 사용 유무에 따라 add 사용시 동작이 달라집니다.
 

```php

$store = new Buffer(new RelationDatabase(null, new Database('localhost', 3306, 'dbName, new Auth('id', 'password'))));
       
$object = new Item();
$object->id1 = 1;
$object->id2 = 1;
$object->id3 = 1;
$object->attr1 = 1;
$object->attr2 = 1;
$object->attr3 = 1;

$ret = $store->get($object);
$ret = $store->set($object);
$ret = $store->add($object);
$ret = $store->remove($object);
$ret = $store->commit($data = null);

```

other complex example

```php

$store = new Buffer(
            new Memcached(
                new RelationDatabase(null, new Database('localhost', 3306, 'dbName, new Auth('id', 'password')))
                , array(new \battlecook\Config\Memcache('localhost'))));
```


## License

DataCooker 는 MIT 라이센스를 사용합니다.




