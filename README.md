# ferdoc
A Docker helper for Laravel

## About ferdoc

A simple application that allows you to run multiple Laravel application within its own docker container. 
This application also makes use of jwilder/nginx-proxy which will allow you to access all of your web applications via foo.test.

### Installation:

    composer global require deferdie/ferdoc
    
If on Windows please run this in powershell: $Env:COMPOSE_CONVERT_WINDOWS_PATHS=1

    $Env:COMPOSE_CONVERT_WINDOWS_PATHS=1

### Usage
CD in your project folder

### create a .env file
Within your project .env file add the following line: 

    APP_NAME=YOURAPPNAME

Please make sute that your APP_NAME is unique for each project.

RUN

    ferdoc docker init
    
After you finish answering the questions you will have a docker-compose.yml file in your project root and a docker directory containing all of your containers

RUN

    ferdoc build

This build the images for your container

RUN

    ferdoc run

Starts all of your containers, you can now access the site in your browser


## Multiple websites
Run the same steps as above with different ports for nginx and mysql then run the below command

RUN

    ferdoc proxy start

## License

ferdoc is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
