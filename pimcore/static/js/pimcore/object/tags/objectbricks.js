/**
 * Pimcore
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.pimcore.org/license
 *
 * @copyright  Copyright (c) 2009-2010 elements.at New Media Solutions GmbH (http://www.elements.at)
 * @license    http://www.pimcore.org/license     New BSD License
 */

pimcore.registerNS("pimcore.object.tags.objectbricks");
pimcore.object.tags.objectbricks = Class.create(pimcore.object.tags.abstract, {

    type: "objectbricks",
    dirty: false,
    addedTypes: {},
    preventDelete: {},

    initialize: function (data, layoutConf) {
        this.addedTypes = {};
        this.preventDelete = {};

        this.data = [];
        this.currentElements = {};
        this.layoutDefinitions = {};
        this.dataFields = [];
        this.layoutIds = [];
        
        if (data) {
            this.data = data;
        }
        this.layoutConf = layoutConf;
    },

    loadFieldDefinitions: function () {
        this.fieldstore = new Ext.data.JsonStore({
            autoDestroy: false,
            url: "/admin/class/objectbrick-list",
            root: 'objectbricks',
            idProperty: 'key',
            fields: ['key', {name: "layoutConfigurations", convert: function (v, rec) {
                this.layoutDefinitions[rec.key] = rec.layoutDefinitions;
            }.bind(this)}],
            listeners: {
                load: this.initData.bind(this)
            },
            baseParams: {
                class_id: this.object.data.general.o_classId,
                field_name: this.myName
            }
        });
        
        this.fieldstore.load();

    },

    getLayoutEdit: function () {
        
        this.loadFieldDefinitions();
        
        var panelConf = {
            autoHeight: true,
            cls: "object_field"
        };
        if(this.layoutConf.title) {
            panelConf.title = this.layoutConf.title;
        }
        
        this.layout = new Ext.Panel(panelConf);
        return this.layout;
    },
    
    initData: function () {
        
        if(this.data.length < 1) {
            this.layout.add(this.getControls());
        } else {
            for (var i=0; i<this.data.length; i++) {
                this.addBlockElement(i,this.data[i].type, this.data[i].data, true);
            }
        }
        
        this.layout.doLayout();
    },
    
    getControls: function (blockElement) {
        
        var collectionMenu = [];

        this.fieldstore.each(function (blockElement, rec) {

            if(!this.addedTypes[rec.data.key]) {
                collectionMenu.push({
                    text: ts(rec.data.key),
                    handler: this.addBlock.bind(this,blockElement, rec.data.key),
                    iconCls: "pimcore_icon_objectbricks"
                });
            }

        }.bind(this, blockElement));
        
        var items = [];
        
        if(collectionMenu.length == 1) {
            items.push({
                cls: "pimcore_block_button_plus",
                iconCls: "pimcore_icon_plus",
                handler: collectionMenu[0].handler
            });
        } else if (collectionMenu.length > 1) {
            items.push({
                cls: "pimcore_block_button_plus",
                iconCls: "pimcore_icon_plus",
                menu: collectionMenu
            });
        } else {
            items.push({
                xtype: "tbtext",
                text: t("no_further_objectbricks_allowed")
            });
        }
        
        var toolbar = new Ext.Toolbar({
            items: items
        });
        
        return toolbar;
    },

    getDeleteControl: function(type, blockElement) {
        if(!this.preventDelete.type) {
            var items = [];
            items.push({
                cls: "pimcore_block_button_minus",
                iconCls: "pimcore_icon_minus",
                listeners: {
                    "click": this.removeBlock.bind(this, blockElement)
                }
            });
            var toolbar = new Ext.Toolbar({
                items: items
            });

            return toolbar;
        }
        return null;
    },
    
    addBlock: function (blockElement, type) {
        
        var index = 0;

        this.addBlockElement(index, type)
    },
    
    removeBlock: function (blockElement) {
        
        var key = blockElement.key;
        this.currentElements[key].action = "deleted";
        
        this.layout.remove(blockElement);
        this.addedTypes[blockElement.fieldtype] = false;
        this.layout.remove(this.layout.get(0));
        this.layout.insert(0, this.getControls());
        this.layout.doLayout();

        this.dirty = true;
        
    },
    

    addBlockElement: function (index, type, blockData, ignoreChange) {
        if(!type){
            return;
        }
        if(!this.layoutDefinitions[type]) {
            return;
        }
        
        this.dataFields = [];
        this.currentData = {};
        
        if(blockData) {
            this.currentData = blockData;
        }

        var blockElement = new Ext.Panel({
            bodyStyle: "padding:10px;",
            style: "margin: 0 0 10px 0;",
            layout: "pimcoreform",
            autoHeight: true,
            border: false,
            items: this.getRecursiveLayout(this.layoutDefinitions[type]).items
        });


        this.layout.remove(this.layout.get(0));

        this.addedTypes[type] = true;

        var control = this.getDeleteControl(type, blockElement);
        if(control) {
            blockElement.insert(0, control);
        }
        
        blockElement.key = type; //this.currentElements.length;
        blockElement.fieldtype = type;
        this.layout.add(blockElement);
        this.layout.insert(0, this.getControls());


        this.layout.doLayout();
        
        
//        this.currentElements.push({
//            container: blockElement,
//            fields: this.dataFields,
//            type: type
//        });

        this.currentElements[type] = null;
        this.currentElements[type] = {
                            container: blockElement,
                            fields: this.dataFields,
                            type: type
                        };

        if(!ignoreChange) {
            this.dirty = true;
        }

        this.dataFields = [];
        this.currentData = {};
    },

    getDataForField: function (name) {
        return this.currentData[name];
    },

    getMetaDataForField: function(name) {
        return null;
    },

    addToDataFields: function (field, name) {
        this.dataFields.push(field);
    },

    addFieldsToMask: function (field) {
        this.object.edit.fieldsToMask.push(field);
    },
    
    getLayoutShow: function () {

        this.layout = this.getLayoutEdit();
        this.layout.disable();

        return this.layout;
    },

    getValue: function () {
        
        var data = [];
        var element;
        var elementData = {};

        console.log(this.currentElements);

        var types = Object.keys(this.currentElements);
        for(var t=0; t < types.length; t++) {
//        for(var s=0; s<this.layout.items.items.length; s++) {
            elementData = {};
//            if(this.currentElements[this.layout.items.items[s].key]) {
            if(this.currentElements[types[t].key]) {
//                element = this.currentElements[this.layout.items.items[s].key];
                element = this.currentElements[types[t].key];

                if(element.action == "deleted") {
                    elementData[element.fields[u].getName()] = "deleted";
                } else {
                    for (var u=0; u<element.fields.length; u++) {
                        if(element.fields[u].isDirty()) {
                            elementData[element.fields[u].getName()] = element.fields[u].getValue();
                        }
                    }
                }


                data.push({
                    type: element.type,
                    data: elementData
                });
            }
        }
        
        return data;
    },

    getName: function () {
        return this.layoutConf.name;
    },

    isDirty: function() {
        console.log(this.dirty)
        return this.dirty;
    }    
});

pimcore.object.tags.objectbricks.addMethods(pimcore.object.helpers.edit);