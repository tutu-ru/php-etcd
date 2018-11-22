# Библиотека для взаимодействия с etcd

Является оберткой над библиотекой [linkorb/etcd-php](https://github.com/linkorb/etcd-php).
Внутри сделана дополнительная обработка некоторых ошибок и несколько методов. 

## Тестирование

Тесты требуют запущенного etcd на localhost:2379.
Запустить проще всего через docker:
```bash
docker run -d --name etcd-test -p 2379:2379 quay.io/coreos/etcd:v2.2.0 -name unittest -listen-client-urls http://0.0.0.0:2379 -advertise-client-urls http://0.0.0.0:2379 -listen-peer-urls http://0.0.0.0:17001 -initial-advertise-peer-urls http://0.0.0.0:17001 --initial-cluster 'unittest=http://0.0.0.0:17001' --initial-cluster-state=new
```
