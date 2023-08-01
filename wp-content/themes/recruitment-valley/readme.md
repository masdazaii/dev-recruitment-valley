# Theme Documentation

- [Read Gulp Documentation](./gulpfile.md)
- [Read Gulp Options Documentation](./compiler.options.md)
- [Read Browserlist Documentation](https://github.com/browserslist/browserslist#browserslist-)

## ES6 Import Alias

If you want to add or change the import alias you can modify [jsconfig.json](./jsconfig.json) file, inside the `paths` property.

That file will be used for compilation process and set auto-complete for your IDE of choice.

#### Indexed root vs non-indexed root

In the [jsconfig.json](./jsconfig.json) file you might notice there's 2 aliases for the same folder, ex:

```javascript
"@bd-core/*": ["./assets/js/src/core/*"],       // non-indexed root
"@bd-core": ["./assets/js/src/core/index.js"],  // indexed root
```

If you have this structure:

```
assets/js/src/themes
|-- index.js
|-- core
    |-- index.js
    |-- something.js

```

whereas

```javascript
// assets/js/src/themes/index.js
import { something } from "@bd-core";
something();
```

```javascript
// assets/js/src/themes/core/index.js
export * from "./something.js";
```

```javascript
// assets/js/src/themes/core/something.js
export const something = () => console.log("something");
```

This is what we meant by **Indexed Root**, notice we're not importing from `@bd-core/something`, but we importing from `@bd-core` instead.

This is a common pattern with [named export](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/export#description), if you want to leverage this pattern therefore you need the following

```javascript
"@bd-core": ["./assets/js/src/core/index.js"],
```

As for the non-indexed root, you're importing from `@bd-core/something` directly, it will looks like this:

```javascript
// assets/js/src/themes/index.js
import { something } from "@bd-core/something";
something();
```

If you want to leverage this pattern therefore you need the following:

```javascript
"@bd-core/*": ["./assets/js/src/core/*"],
```

But to keep things flexible and easy therefore we added them both into the configuration, so keep in mind to always add this 2 aliases together.
