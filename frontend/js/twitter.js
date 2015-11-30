var TwitterModel = Backbone.Model.extend({
  defaults: {
    what: '',
    where: 'html'
  },
  initialize: function(opts) {
    this.collection = opts.collection;
    this.filtered = new Backbone.Collection(opts.collection);
    this.on('change:what', this.filter);
  },
  filter: function() {
    var what = this.get('what').trim(),
      searchFor = 'html',
      models;

    if (what==='' || !what) {
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

var CollectionView = BaseView.extend({
  initialize: function(opts) {
    this.template = opts.template;
    this.listenTo(this.collection, 'reset', this.render);
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
    return this;
  }
});

var FormView = Backbone.View.extend({
  events: {
    'keyup input[name="what"]': _.throttle(function(e) {
      this.model.set('what', e.currentTarget.value);
    }, 200)
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

twitterCollection.fetch({
  success: function () {
    $('#twitterList').html(listView.render().el);
  }
});

//var TwitterRouter = Backbone.Router.extend({
//
//  routes: {
//    "": "displayTweets"
//  },
//
//  displayTweets: function() {
//
//  //  var twitterListView = new TwitterListView({model:twitterCollection});
//
//
//
//    twitterCollection.fetch({
//      success: function () {
//         $('#twitterList').html(twitterListView.render().el);
//
//
//      }
//    });
//
//  }
//
//});
//
//var twitterRouter = new TwitterRouter();
//Backbone.history.start();