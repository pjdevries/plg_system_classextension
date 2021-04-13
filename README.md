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

* The `onAfterInitialise` handler of the system plugin processes the specifications in the JSON file that don't have 
  spefic routes. The `onAfterRoute` handler processes the specifications that do have specifc routes.
* For each extended class file found, a copy of the original class file is created. The path of that file is composed as follows:
    * The directory for the copied file is the base path of the original file, relative to the website root, and 
      prefixed with `[web root]/templates/class_extensions`
    * If a route specification exists (see [JSON specifications](#json-spec) below), the name of that route is appended to the new directory. 
    * The filename of the copy is that of the original class file, but with `ExtensionBase` appended to it. 
    * So for the example above, this will result in the file 
      `[web root]/templates/class_extensions/components/com_content/models/ContentModelCategoryExtensionBase`.
* If a copy already exists and the original class file is newer than the existing copy, the old copy is overwritten with
  the newer version.
* The name of the class in the copied file gets `ExtensionBase` appended to it. So for the example above, this wilt 
  result in `class ContentModelCategoryExtensionBase`.
* Using `include_once`, the copied class, with the new name, is loaded first, followed by the extended class, having the
  same name as the original class.
* Because the system plugin is the first to load the class, later references to the same class will use the already 
  loaded class definition.

## <a id="json-spec">JSON specification</a>

File `[web root]/templates/class_extensions/class_extensions.json` contains JSON encoded information about the (core) 
classes to be extended. It contains an array of objects. Each object describes a single class to be extended. At 
the moment of this writing, an object description contains the following attributes:
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

`route` an optional set of attributes, describing the route to match for the extended class for be effectuated. If not 
present, the extended class is always in effect.

`route.name`: the name of the subdirectory to be added to the default path, when looking for an extended class 
definition and where the original class is copied to.

`route.option`, `route.view` and `route.layout`: the values to compare to the request parameters with the same names, 
when determining if a route matches.     

## Credits

The idea for this plugin came from an earlier prototype by [Herman Peeren](https://github.com/HermanPeeren).