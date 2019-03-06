# natterbase_test
This is a RESTFUL API that uses jwt for authentication, built with CodeIgniter for performing CRUD operations on a Country resource. Though users need to be registered first. The api also logs the activities of the user. The user can view the activities, which shows five per page.

The database is natterbase.sql. Kindly import the database to use this API.

#1 /Signup (POST)
Requires the parameters below:
1. firstname
2. lastname
3. date_of_birth (Date of birth should be in this format "yyyy-mm-dd")
4. email
5. username
6.password

#2 /login (POST)
Login with the parameters below:
1. username
2. password
It generates a token. Copy the token into the header you are using to test the API, using the settings below:
Key - Authorization
Value - [TOKEN_GENERATED]

#3 /countries (POST)
Creates Country. Requires the parameters below
1. name
2. continent

#4 /countries (GET)
Gets all the countries

#5 /countries/(:num)/delete
Delete Country with the id

#6 /activities/(:num)
Gets all the activities. The num is the page number. It gets five activities per page
