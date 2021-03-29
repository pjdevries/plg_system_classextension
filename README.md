# Joomla! Core Class Extension Plugin

This system plugin allows for extension of Joomla! core classes.

## What to do

This is what you must do after installation and activation of the plugin:

### Create an extended class file
* Create a file with the code of your extended class in folder `[web root]/templates/class_extensions`. The name of 
  that file must be identical to the name of the class you are extending. The path of the file below the before mentioned 
  folder must be the same as the path of the file containing the class to be overridden, relative to the website root.
* The code in the extended class file must contain a class definition 
  * with a name  identical to the name of the class you are extending and
  * extending a class with that same class name but with `ExtensionBase` appended to it.
* Add the JSON encoded specifics of the extended class to file `[web root]/templates/class_extensions/class_extensions.json`.

### Example
To create an override of the core Joomla! content category model, do the following:
* Check if folder `[web root]/templates/class_extensions/components/com_content/models` exists and
  create it if it doesn't.
* In the `.../models` folder, create a file for the extended class, named `ContentModelCategory`.
* In the extended class file create the following class definition:

  ```
  class ContentModelCategory extends ContentModelCategoryExtensionBase
  {
      ...
      ...
  }
  ```
* Assuming the file does not yet exist, create file `[web root]/templates/class_extensions/class_extensions.json`.
* Add the following the the JSON file:
  ```
  [
    {
      "class": "ContentModelCategory",
      "file": "components/com_content/models/category.php"
    }
  ]
  ```

## How it works

* The `onAfterRoute` handler of the system plugin processes the specifications in the JSON file.
* For each extended class file found, a copy of the original class file is created. The name of that file is the same 
  as the name of the extended class file, but with `ExtensionBase` appended to it. So for the example above, this will 
  result in the file `[web root]/templates/class_extensions/components/com_content/models/ContentModelCategoryExtensionBase`.
  If a copy already exists, it will only be copied again if the original is newer than the copy.
* The name of the class in the copied file gets `ExtensionBase` appended to it. 
* Using `include_once` we first load the copied original class with the derived class name, followed by the extended 
  class with the name of the original and extending the copied original by its derievd name.
* Because the system plugin is the first to load the class, later uses of the same class will access the already loaded 
  class definition.

## JSON specification

File `[web root]/templates/class_extensions/class_extensions.json` file contains JSON encoded information about core 
classes to be extended. The file contains an array of objects. Each object describes a single class to be extended. At 
the moment of this writing, an object contains the following attributes:
   ```
   {
     "class": ...,
     "file": ...,
     "route": {
        "name": ...,
        "option": ...,
        "view": ...,
        "layout": ...
     }
   }
   ```
`class`: the name of the class to be extended.

`file`: the path of the file containing the class to be extended, relative to the website root.

`route` an optional set of attributes, describing the route to match for the extended class for be effectuated.

`route.name`: the name of the subdirectory to be added to the default path, when looking for an extended class 
definition and where the original class is copied to.

`route.option`, `route.view` and `route.layout`: the values to compare to the request parameters with the same names, 
when determining if a route matches.     
