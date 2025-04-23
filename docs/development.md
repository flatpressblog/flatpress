
# FlatPress: Development & PHPStan configuration

## Static code analysis with PHPStan

FlatPress uses the [PHPStan](https://phpstan.org/) tool for code quality and debugging.  
With the current configuration, the FlatPress core as well as plugins and custom extensions can be analyzed efficiently and without false positives.

---

### **Why this configuration?**

- FlatPress and many plugins are based on dynamic, flexible PHP (e.g. global variables, dynamic methods, dynamic properties).
- The template engine used [Smarty](https://www.smarty.net/) also works very dynamically internally.
- Without filtering, PHPStan produces a lot of warnings, which are *not real errors* in its own code.
- **Solution:**  
  → Use of dummy classes (stubs) for Smarty  
  → Exclusion of Smarty core and plugins  
  → Carefully maintained ignore rules for typical patterns  
  → Only *genuine* errors and warnings are displayed in your own FlatPress code

---

### **How our PHPStan setup works**

#### **1. Bootstrap (dummy classes)**
- The file **`phpstan-bootstrap.php`** contains dummy classes for Smarty and relevant methods/properties.
- This way PHPStan “knows” about dynamic methods & properties.
- Is a method/property missing? Simply enter it in the dummy class!

#### **2. Exclusion of third-party code**
- **Smarty** and its plugins are excluded from the scan via `excludePaths`.
- This means that there is no “noise” due to dynamic methods from third-party libraries.

#### **3. ignoreErrors**
- The most important, frequently recurring “warnings” in dynamic or older PHP code are specifically filtered.
- These include dynamic accesses to arrays, frequent “always true/false comparisons” and cosmetic errors in connection with PHPDoc.

#### **4. Analysis of own code only**
- Thanks to the above measures, PHPStan now only provides real hints/bugs in the FlatPress core, custom plugins and individual customizations.

---

### **Execute PHPStan**

```bash
vendor/bin/phpstan analyse
```

All relevant settings are in **`phpstan.neon.dist`**.

---

### **Daily work: Frequently asked questions**

**I suddenly get “undefined method/property” errors for Smarty again!**  
→ Check whether the corresponding method/property is already defined in the dummy in `phpstan-bootstrap.php`.  
→ If not, simply add it - it's completely safe!

**I want to ignore another type of error. Where?**  
→ Add the pattern in `ignoreErrors` in your `phpstan.neon.dist`.

**Can I update FlatPress/Smarty and continue to analyze it?**  
→ Yes!  
→ If new dynamic methods or properties are used, simply add them to the bootstrap.

**I am developing a plugin. Do I need to consider anything special?**  
→ No.  
→ Thanks to the bootstrap, you can use all Smarty APIs as usual - PHPStan knows them “virtually”.

---

### **Maintenance**

- Keep **phpstan-bootstrap.php** up to date (when using new Smarty methods in your own code)
- Only expand **ignoreErrors** specifically (for new “noise” messages)
- Increase **Level** slowly if possible (optional, for better code quality)
- Check **PHP version** regularly (FlatPress supports min. 7.1, target: >=8.4)

---

### **Changelog / Changes to the analysis**

- **2024-04:**  
  - Complete refactoring of the PHPStan config for FlatPress
  - Dummy classes for Smarty
  - Noise filter for typical legacy PHP/Smarty problems

---

**Questions or problems?**  
Contact the FlatPress forum, GitHub or the development team directly!  
Further information: [https://www.flatpress.org](https://www.flatpress.org)

---

**Have fun developing!**  
**The FlatPress development team**
