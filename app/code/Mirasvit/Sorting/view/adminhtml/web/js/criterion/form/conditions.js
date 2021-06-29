define([
    'jquery',
    'knockout',
    'underscore',
    'uiElement'
], function ($, ko, _, Element) {
    return Element.extend({
        defaults: {
            template: 'Mirasvit_Sorting/criterion/form/conditions/tree',
            
            imports: {
                conditions:                '${ $.provider }:data.conditions',
                sortBySource:              '${ $.provider }:sortBySource',
                sortByAttributeSource:     '${ $.provider }:sortByAttributeSource',
                sortByRankingFactorSource: '${ $.provider }:sortByRankingFactorSource',
                sortDirectionSource:       '${ $.provider }:sortDirectionSource'
            },
            
            exports: {
                conditions: '${ $.provider }:data.conditions'
            }
        },
        
        initialize: function () {
            this._super();
            
            _.bindAll(
                this,
                'addItem',
                'removeItem',
                'addNode'
            );
            
            _.each(this.conditions, function (jsonNode) {
                var node = this.newNode(jsonNode);
                
                this.tree.push(node);
            }.bind(this))
        },
        
        initObservable: function () {
            this._super();
            
            this.tree = ko.observableArray();
            
            return this;
        },
        
        newNode: function (items) {
            var node = {
                items: ko.observableArray()
            };
            
            if (items) {
                _.each(items, function (item) {
                    node.items.push(
                        this.newItem(node, item)
                    );
                }.bind(this))
            }
            
            node.items.subscribe(this.sync.bind(this));
            
            return node;
        },
        
        addNode: function () {
            var node = this.newNode();
            
            node.items.push(
                this.newItem(node)
            );
            
            this.tree.push(node);
        },
        
        newItem: function (node, data) {
            var item = {
                node: node,
                
                sortBy:        ko.observable(data ? data.sortBy : 'attribute'),
                attribute:     ko.observable(data ? data.attribute : ''),
                rankingFactor: ko.observable(data ? data.rankingFactor : ''),
                direction:     ko.observable(data ? data.direction : 'asc'),
                weight:        ko.observable(data ? data.weight : 0)
            };
            
            if (data) {
                _.each(data.conditions, function (data) {
                    this.addItem(item, data)
                }.bind(this))
            }
            
            item.sortBy.subscribe(this.sync.bind(this));
            item.attribute.subscribe(this.sync.bind(this));
            item.rankingFactor.subscribe(this.sync.bind(this));
            item.direction.subscribe(this.sync.bind(this));
            item.weight.subscribe(this.sync.bind(this));
            
            return item;
        },
        
        addItem: function (node, data) {
            node.items.push(this.newItem(node));
        },
        
        removeItem: function (item) {
            var node = item.node;
            node.items.remove(item);
            
            if (node.items().length === 0) {
                this.tree.remove(node);
            }
        },
        
        sync: function () {
            var json = [];
            
            _.each(this.tree(), function (node) {
                var jsonNode = [];
                
                _.each(node.items(), function (item) {
                    jsonNode.push({
                        sortBy:        item.sortBy(),
                        attribute:     item.attribute(),
                        rankingFactor: item.rankingFactor(),
                        direction:     item.direction(),
                        weight:        item.weight()
                    })
                });
                
                json.push(jsonNode)
            });
            
            this.set('conditions', json);
        }
    })
});