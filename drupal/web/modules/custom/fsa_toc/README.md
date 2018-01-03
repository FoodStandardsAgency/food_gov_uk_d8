### FSA TOC Customizations

FSA TOC Customizations is a small module with `hook_entity_presave` that locates body field of node and adds `id` attribute to `<h1>`-`<h6>` tags whenever they don't have an id. This was implemented so that internal links would have fixed anchor id's even when the heading text changes.

Functionality originally built by janis.bebritis@wunder.io