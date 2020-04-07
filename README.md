# Foreweather Publisher

Foreweather Abonelerine günlük hava durumu bilgilerini kullanıcının kayıtlı zaman dilimine göre gruplayıp 
yayın yapan servisidir. 
 
# Kurulum

Foreweather Publisher'ı keşfetmek istiyorsanız, geliştirme ortamını kullanmak iyi bir seçim olabilir. 

## Docker

Foreweather Publisher'ın geliştirme sürümünü başlatmanın hızlı bir yolu github reposunu klonlamak ve aşağıdaki 
komutları çalıştırmaktır:

### Docker

```bash

docker rm -f beans
docker run -d --name beans uretgec/beanstalkd-alpine:latest

docker build --no-cache -t zekiunal/foreweather-publisher .
docker push zekiunal/foreweather-publisher

docker rm -f foreweather-publisher
docker run -d --name foreweather-publisher \
    -v $PWD/src:/www \
    -e QUEUE_HOST="beans" \
    -e API_BASE_URL="http://api" \
    --link beans:beans \
    --link fore_api:api \
    zekiunal/foreweather-publisher
    
 docker logs -f foreweather-publisher
 
 
 
```
 
