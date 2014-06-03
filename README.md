Snowflakes
==========

Snowflakes is a Content Management System and Web publishing software.
##The Features includes:

Creating,Editing and managing posts called snowflakes, Snowflakes events and
Snowflakes gallery keeping in mind Role based access(RBA) for users which 
consists of 
**Author/Editor** - Can Create snowflakes,snowflake Events and gallery but not publish
**Publisher** - Can do all the roles of an Author/Editor and publish and un-publish snowflakes and can only add, edit, veiw or delete own snowflakes
**Manager** - Can do all the role of the publisher and also add, edit, view or delete all snowflakes
**Administrator** - Can do everything a manager can do as well as add and remove users
**Super Administrator** - Can do everything an Administrator can do and also change system settings for Snowflakes

Snowflakes consists of read only API to allow user to show snowflakes data on a personal website, by requesting data and what format the data should be in, usually  HTML, XML or JSON and sometimes jsonhtml by calling the API e.g snowflakesv2.cyrilinc.co.uk/api/snowflake.xml or snowflakesv2.cyrilinc.co.uk/api/snowflake.json.
One can also call the API in correspondence to the above example snowflakesv2.cyrilinc.co.uk/api/index.php?sfty=event&cty=jsonhtml 
**Note** that the 'jsonhtml' is just the recommended structured html format of the requested Snowflakes object in Json code.

Other parts of the API are in Out.php files in the starting snowflakes directory and is always in html format.
To see all published snowflakes i will use e.g snowflakesv2.cyrilinc.co.uk/Out.php, snowflakesv2.cyrilinc.co.uk/Events/Out.php for events and snowflakesv2.cyrilinc.co.uk/Gallery/Out.php for Gallery all of which contains pagination with **First**,**Previous**,**Next** and **Last** buttons to allow user to view more data.

