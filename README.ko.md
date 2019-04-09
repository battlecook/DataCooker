# DataCooker


## 프로젝트 설명

DataCooker 는 다양한 저장소에 접근하는 방법을 추상화해서 몇개의 인터페이스로 제공하는 라이브러리 입니다. 

데이타 저장소들에 접근하는 개별 라이브러리들 (\pdo, \Redis, \Memcached 등)을 추상화 하여 인터페이스를 제공합니다. 

현재 제공중인 데이타 저장소 목록은 다음과 같습니다.

관계형 데이타베이스 : Pdo 라이브러리에서 관리하는 데이타베이스들 

키 벨류 저장소 : memcached, redis, apcu

파일 : [PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet) 라이브러리 에서 제공하는 파일 포맷 ( 읽기만 제공 )

README 지원언어 : [English](README.md)

## 설치하는 방법

packagist.org 에 업로드 예정 입니다.

## 사용하는 방법

정의한 데이터베이스 테이블

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

정의된 클래스

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

클래스의 어노테이션으로 데이터의 속성을 구분합니다.

어노테이션으로 표현 할 수 있는 속성엔 다음의 3가지가 있습니다.

* @dataCookerIdentifier : 데이터의 필수 항목입니다. 복합키를 나타냅니다. 한번 이상은 선언하여야 합니다.

* @dataCookerAttribute : 데이터의 필수 항목입니다. 속성을 나타냅니다. 한번 이상은 선언하여야 합니다.

* @dataCookerAutoIncrement : 데이터의 선택 항목입니다. 자동증가값을 나타냅니다.

DataStore 에는 6가지 인터페이스(get, search, set, add, remove, commit)를 제공합니다. 

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

Memcached 와 RelationDatabase 를 같이사용 했을때의 예시

##### before #####
```
수행전 데이터베이스 상태 
  
+-----+-----+-----+-------+-------+-------+
| id1 | id2 | id3 | attr1 | attr2 | attr3 |
+-----+-----+-----+-------+-------+-------+
|  1  |  1  |  1  |   1   |   1   |   1   |
+-----+-----+-----+-------+-------+-------+
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
수행 후 데이터 베이스 상태
  
+-----+-----+-----+-------+-------+-------+
| id1 | id2 | id3 | attr1 | attr2 | attr3 |
+-----+-----+-----+-------+-------+-------+
|  1  |  1  |  1  |   1   |   1   |   1   |
+-----+-----+-----+-------+-------+-------+
|  1  |  1  |  2  |   1   |   1   |   1   |
+-----+-----+-----+-------+-------+-------+
```

버퍼 데이터 저장소 : 

버퍼 데이터 저장소는 다른 저장소의 저장소로는 사용 될 수 없습니다. 

버퍼 데이터 저장소는 기본적으로 php 메모리에 데이터를 저장하게 됩니다.

다른 저장소와 혼용해서 사용 시 동작방식이 조금 다릅니다.

버퍼 데이터 저장소 는 get set add remove 호출시, 최초 1회 다른 저장소에서 데이터를 가져와 php 메모리에 적재합니다. 

그 후 commit() 함수가 수행 되기 전까지 php 메모리에서만 작업을 진행합니다.

지금까지 수행했던 인터페이스의 수행을 다른 데이터 저장소에 적용을 원한다면 commit 함수를 호출하여야 합니다. 

버퍼 데이터 저장소를 사용하고 클래스에 @dataCookerAutoIncrement 가 정의 되어 있다면 관계형 데이터 베이스에서 autoIncrement 값을 증가하고 그 값을 사용하기 위해서 다른 DataStore 에서는 add 함수를 선 처리 합니다.

정의되어 있지 않다면 다른 함수들과 마찬가지로 후 처리 합니다.

##### before #####
```
수행 전 데이터 베이스 상태
  
+-----+-----+-----+-------+-------+-------+
| id1 | id2 | id3 | attr1 | attr2 | attr3 |
+-----+-----+-----+-------+-------+-------+
|  1  |  1  |  1  |   1   |   1   |   1   |
+-----+-----+-----+-------+-------+-------+
```

##### progress1 #####
```php
$store =  new Buffer(new RelationDatabase(null, new Database('localhost', 3306, 'dbName, new Auth('id', 'password'))));
             
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
수행 후 데이터 베이스 상태
  
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
수행 후 데이터 베이스 상태

+-----+-----+-----+-------+-------+-------+
| id1 | id2 | id3 | attr1 | attr2 | attr3 |
+-----+-----+-----+-------+-------+-------+
|  1  |  1  |  1  |   2   |   2   |   2   |
+-----+-----+-----+-------+-------+-------+
```

## 라이센스

DataCooker 는 MIT 라이센스를 사용합니다.




