# IO – The cornerstone for plentymarkets 7 template plugins

**IO** is the official logic plugin for the default online store of plentymarkets 7. In the new online store for plentymarkets 7, design and logic are separated from each other. Two plugins are required to integrate the online store into your plentymarkets system. The **Ceres** plugin contains the new standard design of the online store and can be customised to meet your needs. The **IO** plugin contains the logic part of the online store, is a general basis for all design plugins and can also be used by other plugins.

## Setting up IO in plentymarkets

The **IO** plugin only needs to be provisioned in plentymarkets. No additional setup is required.

<div class="alert alert-warning" role="alert">
  It is absolutely essential, however, that <b>IO</b> is assigned the highest <b>Plugin position</b> (e.g. 999) via the <b>Set position</b> action in the plugin overview.
</div>

 **IO** is a required plugin for the **Ceres** template plugin.

<div class="alert alert-danger" role="alert">
    When deploying the plugin <b>IO</b> in <b>Productive</b>, the old plentymarkets online store will be unavailable. <b>IO</b> will use the URL of the online store.
</div>

## License

This project is licensed under the GNU AFFERO GENERAL PUBLIC LICENSE. – find further information in the [LICENSE.md](https://github.com/plentymarkets/plugin-io/blob/stable/LICENSE.md).
