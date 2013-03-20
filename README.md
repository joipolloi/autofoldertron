# Autofoldertron

Based on [ncrossland's autofolders](http://github.com/ncrossland/modx-autofolders-revo) for MODx Evo and Revo, this plugin will create a folder structure of MODx resources based on a date.

This is primarily used for News, Events or Blog sections on a website that makes sense to have in date based folders.

## Example

To take a News section example, you have a news landing page that lists all of the news items and the individual news story.

The landing page URL could be http://www.rckt.co.uk/news/ and you want the story URL to be http://www.rckt.co.uk/news/2013/01/news-story.

This is where Autofoldertron is useful. Instead of manually creating the 2013 and 01 resources in MODx, those resources are automatically created based on the news story's publish date.

For instance:

```
-> News landing page
```

After creating a resource beneath news landing page:

```
-> News landing page http://www.rckt.co.uk/news/
-> -> 2013 (auto generated) http://www.rckt.co.uk/news/2013/
-> -> -> 01 (auto generated) http://www.rckt.co.uk/news/2013/01/
-> -> -> -> News story http://www.rckt.co.uk/news/2013/01/news-story
```

## Advantages over autofolders

All of this sounds a lot like autofolders, why the new plugin?

For one, if you wanted more than one autofolder managed area, you had to duplicate the plugin and fill in new values. For large sites with multiple or flexible autofolder areas this was difficult to manage.

Autofoldertron solves this by setting one or more templates as a "parent", rather than autofolder's single resource.

Autofoldertron also allows for any date based formats for titles and aliases.

## Usage

Install the plugin, set a value for ```parent_templates``` and create a resource with that template.

By default creating any resources directly beneath that resource will shuttle it into a year / month folder structure.

If you want more fine grained control over what resources are managed, set the ```filter_templates``` value

## Installation

TODO

### Options

* **parent_templates** Comma separated list of template IDs which you want to act as autofoldertron managed. Defaults to blank, must be provided for autofoldertron to function.
* **generated_template** The ID of the template (can be blank) which generated pages will be assigned
* **folder_structure** The structure of the folders, defaults to 'y/m'. Valid parts are "y" (year), "m" (month) and "d" (day) and valid path separator is "/"
* **filter_templates** By default autofoldertron will organise any and all resources that are created beneath it, set this value to a comma separated list of template IDs and only resources with those templates will be managed by autofoldertron
* **date_field** The MODx date field which will be used to organise the resource. Defaults to "publishedon"
* **[year|month|day]_alias_format** The PHP date compatible format that will be used for generated page's alias. Defaults to "Y", "m" and "d" respectively
* **[year|month|day]_title_format** The PHP date compatible format that will be used for generated page's title, longtitle and menutitle. Defaults to "Y", "F" and "d" respectively

## Quirks

If you change any of the options be sure to clear the MODx site cache else the options may not filter through.

As with autofolders, it is entirely possible to create a recursive loop with two resources being set as their own parents. This will essentially remove the resources from the manager site tree and will have unexpected consequences on the frontend. Autofoldertron attempts to prevent this but if it happens, your only recourse is to dive into the database and manually sort it or restore from a recent backup.
