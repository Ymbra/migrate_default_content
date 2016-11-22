# Overview

Provide default test content for a Drupal site using migrate.

This module use csv as a data source.
This csv are located in a folder specified by a setting called source_dir.
The name of the csv follows a standard: ENTITY_TYPE.BUNDLE.csv
like user.user.csv or node.article.csv

You can find examples for most entity types in the
example_default_content folder.

Any field with the password data type will be hashed automatically.

# Entity references

Entity reference configurable fields and base fields will try to add
dependencies automatically from other csv present.
For example, if you have a user migration the author of you nodes
will be lookep up in the user migration.
The first column of any csv will be used as the identifier for that
migration.

If a entity reference field is not able to determine the bundle it
should reference you can specify it in the name of the field like thi

title,uid,body,field_related:article
Hello world,demo,Body,My article

# Files

If there's a "files" directory inside your source directory, for example:
/default/content/files
A migration for those files will be automatically created and files can
be referenced by their file name in the following way:

title,uid,body,field_image
Hello article,demo,Body,magic.png

Be sure you have a magic.png file in your "files" folder.

# Menu link content

Since this entity type has hierarchy you might want to specify
a uuid so you can set parents like this:

title,link,menu_name,weight,parent,uuid
Admin,internal:/admin,main,0,,9250aef9-a7b9-43f1-ae49-4e872bcceb7f
Extend,internal:/admin/modules,main,0,menu_link_content:9250aef9-a7b9-43f1-ae49-4e872bcceb7f

TODO: be able to set a menu item for an entity (e.g. a node) like
entity:node/76
but using an id that is not changing like the uuid
Meanwhile you can set path for you nodes an internal links to it like
internal:/about-us


# Multivalue fields

Use a escaped JSON array like this:
demo2,demo2,demo2@demo.com,1,"[\"administrator\",\"editor\"]"

Multicomponent fields

Some fields such as text_with_summary have many components:
value, format and summary for this specific field.
You can map them in your CSV header as "body" and the text will be migrated.

However if you want different values for the different subcomponents
they have to be specified in the CSV header file by capitalizing the
Subcomponent like this:
title,uid,bodyValue,bodyFormat,bodySummary,field_imageTarget_id,field_imageAlt,field_related

You can also use a escaped JSON array like this for "body" instead of changing the headers:

Hello page 2,demo,"{\"value\":\"<p>ffff<\/p>\"\,\"format\":\"full_html\",\"summary\":\"xxxxx\"}"

You will have to specify all of the subfields on the JSON due to this bugs:
https://www.drupal.org/node/2639556
https://www.drupal.org/node/2632814
so just "value" and "format" is not enough
