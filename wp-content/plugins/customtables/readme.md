## WordPress Custom Tables
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Description
The Custom Tables plugin allows you to create, manage, and display custom data on your WordPress website.
It is helpful if you need to display data that is not part of the standard WordPress content structure.For example, you could use this plugin to create a custom table for product information, customer data,
or any other type of data that you need to display on your site.

## Installation Manually
1. Download the latest archive and extract to a folder
2. Upload the plugin to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress

## Useful links
https://ct4.us/

https://joomlaboat.com/

# Shortcode Usage

The Custom Tables shortcode allows you to display tables and layouts with various options. Here's how to use it:

## Basic Requirements

- You must provide either a `table` or `layout` parameter (at least one is required)
- If you specify a `table` without a `layout`, you can optionally add a `view` parameter

## Basic Usage

```
[customtables table="1"]                    # Display table #1
[customtables table="countries"]            # Display table "countries", catalog view, default layout
[customtables layout="2"]                   # Display layout #2, the table is associated with the layout
[customtables layout="ListOfCountries"]     # Display layout "ListOfCountries" (layout ID or name can be used)
```

## Views

When using table without layout:

```
[customtables table="countries" view="edit"]        # Add country record 
[customtables table="1" view="details" id="2"]     # Details view of country table, record #2
[customtables table="countries" view="catalog"]     # Catalog view of table "countries" with default layout
```

## Optional Parameters

```
filter="category=books"    # Filter the results
order="title ASC"         # Set the order of results
limit="10"               # Limit number of results
```

## Full Examples

```
[customtables table="countries" layout="CountryList" filter="continent=Europe" order="name ASC" limit="10"]
[customtables table="countries" view="details" id="5" layout="CountryDetails"]
```

**Note:** You can use either numeric IDs or names for both tables and layouts. When using the `details` view, remember to include the `id` parameter to specify which record to display.