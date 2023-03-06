# CamoDB : v0.0
### A simple and free NoSQL database solution that uses json files to efficiently and flexibly store data for your projects.

## How it works:
This database system works very similarly to the normal way of storing files that everyone is used to. This keeps it as simple and
intuitive as possible.

Heres how it's layed out:
- When installed, you are given a central hub that stores all the data for this project and an admin panel that lets you easily manage everything
- Contained inside the hub is your individual databases. Each one of these should be storing a completely different category of data.
- Databases are made up of smaller groups called Collections. Each collection holds individual JSON files called Entries.
- Within those json files you can nest as many json fields and json objects as you'd like to.

An example of this could be a chatting app. The hub would be contain all the data. There could be a database for users and another for messages.
The users database could hold each users data as a collectio, then each entry is various datapoints about the user such as their login info, their profile, and their subscription information. Then in the messages database there could be a series of collections representing chats between users, and each entry of text is an array of the messages for that particular day and the timestamps they were sent at.

