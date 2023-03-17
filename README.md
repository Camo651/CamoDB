# CamoDB : v1.2
> A simple and NoSQL database solution
---
## Table of Contents
- [CamoDB : v1.2](#camodb--v12)
  - [Table of Contents](#table-of-contents)
  - [What is it?:](#what-is-it)
      - [But why?](#but-why)
      - [How do I install it?](#how-do-i-install-it)
  - [Getting started](#getting-started)
  - [Creating and managing databases](#creating-and-managing-databases)
  - [Structure](#structure)
  - [Access Levels](#access-levels)
  - [Users](#users)
        - [User data](#user-data)
  - [API](#api)
        - [USER Requests](#user-requests)
          - [USER Actions](#user-actions)
        - [CALLS Requests](#calls-requests)
          - [CALLS Path](#calls-path)
          - [CALLS Actions](#calls-actions)
        - [Responses](#responses)
          - [Status Codes](#status-codes)
  - [Support](#support)
## What is it?:
CamoDB is a super simple database backend solution that uses `PHP` and `JSON` files to efficiently store your projects data. It's built with simple projects in mind, a quick and dirty way to store data for low traffic websites and hobby projects.

#### But why?
There are already countless databases out there already. Why another? CamoDB was built for use cases like an [iPage](https://www.ipage.com/) website where you don't have access to services like Node.js and don't want to pay for cloud storage services.
It allows data to be stored using PHP alongside all the other website data that you already pay to have hosted!

#### How do I install it?
The installation process is very simple! Just create a new, empty, folder inside your project files and dump all the files in this repository into it. Everything else can be done through the admin panel and a text editor.

---
## Getting started
Once you've installed the files to your new database directory, open up that folder in your browser on the website.
For example, if I install the database in a folder on my personal website such at `myPortfolioSite/Databases/testDatabase`, I could access the admin panel from `www.matthagger.me/Databases/testDatabase`.

Great! Welcome to the admin panel. Now all you need is to set up an admin account.
In the files you can go to `configuration.json` and open it up. It should look something like this:
```json
{
    "admins":{
        "admin":"1234"
    },
    "databasePath":"db",
    "requireAuth":1,
    "allowDynamicAuth": 1,
    "corsUrls":[
        "*"
    ]
}
```

Let's break it down:
- `admins` is a list of all the usernames and passwords for admin accounts that can access the admin panel. Be careful with this, the admin panel can be accessed from anywhere so make sure to set a secure name and password. The default admin account is just a placeholder, you should most definitely change it!
- `databasePath` is the path to the folder where the database files are stored. By default this is set to "db" which is the folder already created for you. If you want to change this, make sure to change it in both the configuration file and create a new folder with the same name in place of the old one.
- `requireAuth` is a boolean value that determines whether or not the admin panel requires authentication. If you set this to 0, anyone can access the admin panel. If you set it to 1, only admin accounts can access the admin panel.
- `allowDynamicAuth` is a boolean value that determines whether or not new accessor accounts can be created. If you set this to 0, only the accounts predefined in the users list can access any of the databases. If you set it to 1, new accounts can be created by anyone with. This is useful if you want to have a website that only set users can access data from or if you want to have a website that anyone can sign up for an account.
- `corsUrls` is a list of URLs that are allowed to access the database. By default this is set to "*" which means that anyone can access the database. If you want to restrict access to only certain websites, list them out in the array. If you want to restrict access to only your website - the one the database is hosted on - then leave it blank.

Some of these settings can also be changed from the admin panel, but it's recommended that you change them in the configuration file instead.

---
## Creating and managing databases
Once you've made a new account and signed into the admin hub, you'll be greeted with a list of all the databases that you have created. If you haven't created any yet, you'll just see a blank page. To create a new database, click the "Create Database" button in the top right corner. A new database with a random name will be created and appear in the list.
To confiure it, click "Manage" on the database you want to configure. You'll be greeted with a page that has these options:
- `Database Name` is the name of the database. This is the name that will be used to access the database from the API.
- `Database Info` is a description of the database. This is just for your own reference and won't be used by the API.
- `Readable?` determines whether or not the database can be read from. If you set this to false, reading from the database will be completely inaccessible from the API.
- `Writable?` determines whether or not the database can be written to. If you set this to false, writing to the database will be completely inaccessible from the API.
- `Requiere Auth?` determines whether or not the database requires authentication to access. If you set this to true, only accounts that have been created in the users list can access the database. If you set this to false, anyone can access the database regardless of whether or not they have an account.
- `Min Read Level` a number 0 - 3 that determines the minimum level of access required to read from the database.
- `Min Write Level` a number 0 - 3 that determines the minimum level of access required to write to the database.
- `Min Delete Level` a number 0 - 3 that determines the minimum level of access required to delete from the database.
  _(See more about access levels below)_

---
## Structure
The database system is structured like a tree, having its root as the `db` directory specified in the `configuration.json` file.

Each database has a folder in the root directory with the name of the database.

Inside this folder is a `dbconfig.json` file that stores the configuration for the database and a `collections` folder that stores all the collections in the database.

Each collection has a folder in the `collections` folder with the name of the collection.

Inside this folder are all the entries in the collection. Each entry is stored as a JSON file with the name of the entry.

Here is an example of what the structure of a database folder looks like:
- `db` - The folder where all the database files are stored.
  - `myDatabase` - An example database with 3 collections
    - `dbconfig.json` - The configuration file for the database. This is where all the    database settings are stored.
    - `collections` - The folder where all the collections are stored.
      - `collection1` - An example collection with 1 entry
        - `Entry1.json` - An example entry
      - `collection2` - An example collection with 3 entries
        - `Entry1.json` - An example entry
        - `Entry2.json` - An example entry
        - `Entry3.json` - An example entry
      - `collection3` - An example collection with 0 entries
  - `myOtherDatabase` - An example database with 0 collections
    - `dbconfig.json` - The configuration file for the database. This is where all the database settings are stored.
    - `collections` - The folder where all the collections are stored.
  

---
## Access Levels
Access levels are a way of controlling who can access the database. There are 4 levels of access to choose from. While databases are reccomended to have the following access levels, you can configure them however you want. 
Note that a user with a higher access level can do everything that a user with an access level lower than it can do.

It is recommended that you use the following access levels:
- `-1` - This is reserved for unknown users. Will be returned when the account can not be found.
- `0` - Minimum access level. (Reccomended for read only access)
- `1` - Medium access level. (Reccomended for read and write access)
- `2` - Maximum access level. (Reccomended for read, write and delete access)
- `3` - Admin access level. (Reserved for admin accounts)

---
## Users
Users are accounts that can access the database. They can be created from the admin panel or from the API. If you set `allowDynamicAuth` to 0 in the configuration file, only the accounts listed in the users list can access the database. If you set it to 1, anyone can create an account from the API.

A User is defined as the following,
- A `UUID` (Unique User ID) - This is a unique identifier for the user. It is generated automatically when the user is created and can not be changed.
- A `username` - This is the username that the user will use to sign in. It can be changed from the admin panel or the API.
- A `password` - This is the password that the user will use to sign in. It can be changed from the admin panel or the API.
- A `permission` level - This is the access level that the user has. It can only be changed from the admin panel.

##### User data
Each user is stored a .json file in the `users/users` folder, named as the user's UUID. To make indexing and querying user data easier, there is a `users/uuidMap.json` file that stores each user's UUID and username as a key value pair. This is used to quickly find a user's UUID when given their username. It will be automatically updated when a user is created, deleted or updated.

These users should only be used in an application to authenticate API calls. Do not use them as a way of storing user data. If you want to store custom user data for your app, create a new database and use the API to write and read data from it with the user's API credentials linked to in. The only data that these users can store is their username, password and permission level.

---
## API
The API is the main way of accessing the database. It is a RESTful API that uses JSON to communicate. All requests must be made to the `index.php` in the root of the database using only POST requests.

There a 2 main types of requests that can be made to the API:
- `USER` - Used to create, update and delete users. Only one request can be processed at a time.
- `CALLS` - Used to read, write and delete data from the database. Multiple requests can be processed at a time.

_Note: Requests need to be in all caps to register_

##### USER Requests
User requests are used to interact with user data.
They consist of:
- `username` - The username of the user to make the request as.
- `password` - The password of the user to make the request as.
- `USER` - Signifies that this is a USER request.
  - `action` - The action to perform. Actions are listed below.
  - `data` - The data to create, update or delete the user with.
    - `perms` - The permission level of the user to create, update or delete.

###### USER Actions
- `create` - Creates a new user.
- `delete` - Deletes an existing user.
- `chngun` - Changes the username of an existing user.
- `chngpw` - Changes the password of an existing user.

Making a user request is as simple as making a POST request to the API with the above data as a JSON string as the body of the request.
An example of how you could create a new user is shown below:
```json
{
    "username": "testUsername",
    "password": "123abc",
    "USER":{
        "action": "create",
        "data":{
            "perms":2
        }
    }
}
```


##### CALLS Requests
Calls requests are used to interact with the database.
They consist of:
- `username` - The username of the user to make the request as.
- `password` - The password of the user to make the request as.
- `CALLS` - Signifies that this is a CALLS request.
  - `path` - The path to the data to read, write or delete.
  - `action` - The action to perform. Actions are listed below.
  - `data` - The data to write to the database.
_Note: If the database is set to not require authentication, you can leave the username and password fields as empty strings, but the keys must still be present._

###### CALLS Path
The `path` field is used to specify the path to the data to read, write or delete. It is a string that is formatted as a directory path. Each part of the path should be seperated by a backslash `/` and should not start or end with a backslash. The path is relative to the `db` folder and each part of it is case sensitive.
An example of a path is `myDatabase/collection1` or `myDatabase/collection1/myEntry1`.

The path does not just need to just be directories. It can also be used to specify a specific field in an entry. Do this by treating fields in the entries as more subtrees of the path. Some examples of this are shown below:
- `myDatabase/collection1/myEntry1/aField`
- `myDatabase/collection1/myEntry1/aFieldWithANumber`
- `myDatabase/collection1/myEntry2/*`

In general, the path can be thought of in this format, with the only required part being the database name:
```text
databaseName/collectionName/entryName/fieldName
```

###### CALLS Actions
- `get` - Gets the data at the specified path.
  - If the path only specifies a database, it will return a list of all collections in that database.
  - If the path only specifies a database and a collection, it will return a list of all entries in that collection.
  - If the path specifies a database, a collection and an entry, it will return a list of all fields in that entry, but not their values.
  - If the path specifies a database, a collection, an entry and a field, it will return the value of that one field. If the field is a star `*`, it will return the entire entry with all the values of the fields.
- `set` - Sets the data at the specified path.
  - Set must always have a database, collection and entry specified in the path.
  - If the entry or collectiong does not exist, it will be created. Databases cannot be created with this action.
- `add` - Appends the data to the specified path.
  - Works the same as `set` but must be called on only fields that store arrays.
  - if the field already exists, it will append the data to the end of the field instead of overwriting it.
- `delete` - Deletes the data at the specified path.
  - Can only be called on fields, entries and collections. Databases cannot be deleted with this action. This action can not be undone once executed.
- `query` - Coming soon... maybe...

CALLS requests can be made by making a POST request to the API with the above data as a JSON string as the body of the request. While the USER request can only have one request in the `USER` object, the CALLS request can have multiple requests in the `CALLS` array. Each request in the array will be processed in order. Make sure to use an array for the `CALLS` object even if there is only one request.
Each request in the `CALLS` array must be a valid call, and while technically you can execute different actions in the same request, it is not recommended. Executing the same action on multiple paths is okay.

A sample CALLS request is shown below:
```json
{
    "username": "testUsername",
    "password": "123abc",
    "CALLS":[
        {
            "path": "myDatabase/colllection1/myEntry1",
            "action": "set",
            "data": {
                "aField": "A value to set a field to",
                "aFieldWithANumber": 20
            }
        },
        {
            "path": "myDatabase/colllection1/myEntry2",
            "action": "set",
            "data": {
                "anotherField": "my1Name",
                "anotherFieldWithANumber": 32
            }
        }
    ]
}
```

##### Responses
Once a request has been made, the API will respond with a JSON string containing the response. The response will contain the following fields:
- `status` - The status code of the response. See below for a list of status codes.
- `message` - A message describing the status code if an error has occurred or 'success' if no error has occurred.
- `data` - The data returned from the request. Will be the requested data if the request was successful or a stack trace if an error has occurred.
  
###### Status Codes
- `200` - Success, no error has occurred.
- `400` - Bad request, the request was malformed.
- `401` - Unauthorized, the username or password was incorrect.
- `403` - Forbidden, the user does not have permission to perform the requested action.
- `404` - Not found, the requested data was not found.
- `500` - Internal server error, an error has occurred on the server.
- `501` - Not implemented, the requested action is not implemented.


## Support
If you need help with the API, you can DM `Camo#0477` on Discord or open an issue on GitHub.