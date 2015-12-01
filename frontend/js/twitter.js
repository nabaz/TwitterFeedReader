var TwitterModel = Backbone.Model.extend({
  defaults: {
    what: '',
    where: 'twitter_content'
  },
  initialize: function(opts) {
    this.collection = opts.collection;
    this.filtered = new Backbone.Collection(opts.collection);
    this.on('change:what', this.filter);
    return this;
  },
  filter: function() {
    var what = this.get('what').trim(),
      searchFor = 'twitter_content',
      models;
    if (jQuery( ".profile-img" ).length > 2) {
      jQuery( "#twitterList" ).hide();
    }else{
      jQuery( "#twitterList" ).show();
    }
    if (what==='') {
      models = this.collection.models;
    } else {
      models = this.collection.filter(function(model) {
        return _.some(_.values(model.pick(searchFor)), function(value) {
          return ~value.indexOf(what);
        });
      });
    }
    this.filtered.reset(models);
  }
});

var BaseView = Backbone.View.extend({
  render:function() {
    var html, $oldel = this.$el, $newel;
    html = this.html();
    $newel=$(html);

    this.setElement($newel);
    $oldel.replaceWith($newel);

    return this;
  }
});

var ItemView = BaseView.extend({
  events: {
    'click': function() {
      console.log(this.model.get('name'));
    }
  }
});

var CollectionView = BaseView.extend({
  initialize: function(opts) {
    this.template = opts.template;
    this.listenTo(this.collection, 'reset', this.render);
    return this;

  },
  html: function() {
    var models = this.collection.map(function (model) {
      return _.extend(model.toJSON(), {
        cid: model.cid
      });
    });
    return this.template({models: models});
  },
  render: function() {
    BaseView.prototype.render.call(this);

    var coll = this.collection;
    this.$('[data-cid]').each(function(ix, el) {
      new ItemView({
        el: el,
        model: coll.get($(el).data('cid'))
      });
    });

    return this;
  }
});

var FormView = Backbone.View.extend({
  events: {
    'keyup input[name="what"]': _.throttle(function(e) {
      this.model.set('what', e.currentTarget.value);
    }, 200),
    load: _.throttle(function(e) {
      console.log('hey');
      this.model.set('what', 'open');
    }, 200),
  }
});

var TwitterCollection =  Backbone.Collection.extend({
  model: TwitterModel,
  url: "../api/index.php"

});

var twitterCollection = new TwitterCollection();

var flt = new TwitterModel({collection: twitterCollection});
var inputView = new FormView({
  el: 'form',
  model: flt
});

var listView = new CollectionView({
  template: _.template($('#template-list').html()),
  collection: flt.filtered
});
$('#content').append(listView.render().el);

var TwitterListView = Backbone.View.extend({
  tagName: "ul",
  render: function(eventName) {
    _.each(this.model.models, function (tweet) {
      $(this.el).append(new TwitterListItemView({model:tweet}).render().el);
    }, this);
    return this;
  }
});

var TwitterListItemView = Backbone.View.extend({
  tagName:"li",
  template:_.template($('#tpl-twitter-item').html()),
  render:function (eventName) {
    $(this.el).html(this.template(this.model.toJSON()));
    return this;
  }
});

var TwitterRouter = Backbone.Router.extend({
 routes: {
   "": "displayTweets"
 },
 displayTweets: function() {
  var twitterListView = new TwitterListView({model:twitterCollection});
   twitterCollection.fetch({
     success: function(model, response, options) {
       $('#twitterList').append(twitterListView.render().el);
     }
   });
 }
});

var twitterRouter = new TwitterRouter();
Backbone.history.start();
