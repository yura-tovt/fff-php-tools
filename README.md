### FFF PHP tools

**Folders**

*examples* - contains examples of config files for tools used in this image.
*keys* - contains ssh keys.

**Keys list**

*deploy* - used in CI/CD for accessing dev, staging and production servers via SSH.

**Help**

*Build docker image*

docker build --no-cache --tag piratto/fff-php-tools:0.4 .

*Push image to docker hub*

docker push piratto/fff-php-tools:0.4


