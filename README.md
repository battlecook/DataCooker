
Data Structure

KeyValue Storage : Tree
Non KeyValue Storage : Array



Supported KeyValue Store

Memcached
Redis

Supported Non KeyValue Store

MySQL ( MariaDB )
Spreadsheet
PhpArray

Data structure conversion between KeyValue and Non KeyValue

get : caller storage conversion 

set, remove : callee storage conversion for optimize

add : this is problem;;;;




set 시에 rdb 를 위해서 attr 중에 어떤게 변했을지 까지 확인할진 추후 고려해 볼것 ( rdb 를 안쓰는 경우도 많을거 같기도 하고 공수가 너무 많이 듬 )



이전 값 저장해뒀따가 비교하는 거 스펙에 넣을것 




코드 생성기 만들것


MemcacheDataSTore 의 경우 트리사이즈가 1M 이 넘지 않게 하면 좋을듯 ( 멤케시 캐시 사이즈 디폴트 크기 )



read only option 넣을 것 




전체 get 하는거 옵션 넣을 것 