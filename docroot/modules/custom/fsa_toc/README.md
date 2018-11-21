### FSA TOC Customizations

FSA TOC Customizations handles table of content links 

* Creates a service (`fsa_toc.service`) with (`renderAnchors()`) to render TOC
Anchor links from HTML markup string.
* Implements `hook_entity_presave` that locates body field of node and adds 
`id` attribute to `<h1>`-`<h6>` tags whenever they don't have an id. This was 
implemented so that internal links would have fixed anchor id's even when the
heading text changes.
* Creates "Table of contents" (`fsa_toc`) block that selectively displays node 
body TOC based on boolean field "Display table of contents" (`field_fsa_toc`) 
value.
