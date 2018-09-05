
# PipeDrive Rest-ful Test  
  
 1. [How To Install](#how-to-install)  
 2. [How To Use](#how-to-use)  
 3. [Using the Unit Test](#using-the-unit-test)
 
## How To Install  
  
 1. Clone the Project using command :   
 `git clone git@github.com:subzerobo/pipedrive_test.git`  
 2. Install the composer if you dont have it already ;-)  
 3. Run `composer install` in the root directory of project  
 4. Create the database using any name you like.  
 5. Edit Database Settings in `config/settings.php` in db section  
```  
 'db' => [         
         'driver' => 'mysql',    
         'host' => '127.0.0.1',    
         'port' => '3306',    
         'database' => "organization",    
         'username' => 'your_username',    
         'password' => "your_password",    
         'charset' => 'utf8',    
         'collation' => 'utf8_unicode_ci',    
         'prefix' => '',    
      ],    
 ```    
5. Load the dump in your database `database\dump.sql`  
 6. Set up virtual host on Apache or Nginx, make sure what DocumentRoot relate on `public` directory   
 7. Alternatively you can just run commnad `composer start` to start project using your local machine PHP: Built-in web server for this url : http://localhost:8080   
   
  
## How To Use  
You can use any tool for making HTTP queries: postman, curl, etc.  
  
### Using Postman Collection   
 1. To test project using the postman collection you can import Collection JSON file in root of project `OrganizationRest.postman_collection.json` in you Postman , you can download it here https://www.getpostman.com/ or just use the web-interface.  
 2. There are 3 [Three] Requests Here :  
          - **Create Organization** [ Create the Sample item provided in test ]  
  - **Get Organization** [Query for sample search parameter : Black Banana]  
  - **Get Organization With Pagination** [Query for sample serach parameter : Black Banana with `limit` query parameter set  
  
### First End Point 1) For testing first end-point [ Organizations and Relations creation ] just send POST request to /organizations url with this body and `JSON (application/json)` body type  
```  
{  
 "org_name": "Paradise Island", "daughters": [{ "org_name": "Banana tree", "daughters": [{ "org_name": "Yellow Banana" }, { "org_name": "Brown Banana" }, { "org_name": "Black Banana" }] }, { "org_name": "Big banana tree", "daughters": [{ "org_name": "Yellow Banana" }, { "org_name": "Brown Banana" }, { "org_name": "Green Banana" }, { "org_name": "Black Banana", "daughters": [{ "org_name": "Phoneutria Spider" }] }] }]}  
```  
2) For testing second end-point (getting relation of Organization) just send GET request to /organizations/{organizationName} url (organizationName should be urlencoded if you are not using browser to test it )  
You can use pagination parameters: just use limit and page parameters. For example: `/organizations/Black Banana?limit=3&page=1`. Default values for `limit` is `100` and for the `page` is `1`.

## Using the Unit Test
to do php unit tests just use `composer test` command in the root directory.