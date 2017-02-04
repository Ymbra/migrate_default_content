# Overview

Provide default test content for a Drupal site using migrate.

This module uses csv or yaml files as a data source.
These files are located in a folder specified by a setting called source_dir.
The name of the files follows a standard: ENTITY_TYPE.BUNDLE.FILE_TYPE
like user.user.csv or node.article.yml

You can find examples for most entity types in the
example_default_content_csv and example_default_content_yml folders.

Any field with the password data type will be hashed automatically.

# Entity references

Entity reference configurable fields and base fields will try to add
dependencies automatically from other file present.
For example, if you have a user migration the author of you nodes
will be lookep up in the user migration.
The first column (or element) of any file will be used as the identifier for that
migration.

If an entity reference field is not able to determine the bundle it
should reference you can specify it in the name of the field like this

title,uid,body,field_related:article
Hello world,demo,Body,My article

Entity reference revision fields (mainly to give support to paragraphs) behave
roughly the same.

# Files

If there's a "files" directory inside your source directory, for example:
/default/content/files
A migration for those files will be automatically created and files can
be referenced by their file name in the following way:

```csv
title,uid,body,field_image
Hello article,demo,Body,magic.png
```

Be sure you have a magic.png file in your "files" folder.

You can also create your own files migration in case you need more
fields like uuid or the author of the image.

In a file.file.yml you can do like the following
```yml
-
   uuid: "368f014b-1eed-44f4-850e-cb8e08ad4753"
   filename: "test.jpg"
   source_full_path: "/tmp/test.jpg"
   destination_full_uri: "public://custompath/test.jpg"
```
So the source path and the destination path can be custom also.
Later the module will convert both in the 'uri' field of the file entity.

# Menu link content

Since this entity type has hierarchy you might want to specify
a uuid so you can set parents like this:

```
title,link,menu_name,weight,parent,uuid
Admin,internal:/admin,main,0,,9250aef9-a7b9-43f1-ae49-4e872bcceb7f
Extend,internal:/admin/modules,main,0,menu_link_content:9250aef9-a7b9-43f1-ae49-4e872bcceb7f
```

TODO: be able to set a menu item for an entity (e.g. a node) like
entity:node/76
but using an id that is not changing like the uuid
Meanwhile you can set path for you nodes an internal links to it like
internal:/about-us

Multicomponent fields

Some fields such as text_with_summary have many components:
value, format and summary for this specific field.
You can map them in your file as "body" and the text will be migrated.

However if you want different values for the different subcomponents
they have to be specified in the file by capitalizing the
Subcomponent like this:
```
title,uid,bodyValue,bodyFormat,bodySummary,field_imageTarget_id,field_imageAlt,field_related
```

Not so much escaping is needed if you use a yaml format file.

You will have to specify all of the subfields on the JSON due to this bugs:
https://www.drupal.org/node/2639556
https://www.drupal.org/node/2632814
so just "value" and "format" is not enough


# Translation support

The name of the files that define a translations follows the scheme: ENTITY_TYPE.BUNDLE.LANGCODE.FILE_TYPE
like or node.article.es.yml, beeing the original migration node.article.yml

There must exist always the field 'translation_origin', which references to the key of the origin content.

See the examples: node.article.yml and node.article.es.yml in the example_default_content_yml folder.