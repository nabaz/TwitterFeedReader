var TwitterModel = Backbone.Model.extend();

var TwitterCollection = Backbone.Collection.extend({

  model: TwitterModel,
  url: "../api/index.php"

});

var TwitterListView = Backbone.View.extend({

  tagName: "ul",

  render: function(eventName) {
    _.each(this.model.models, function (msg) {
      $(this.el).append(new TwitterListItemView({model:msg}).render().el);
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

    var twitterCollection = new TwitterCollection();

    var twitterListView = new TwitterListView({model:twitterCollection});
    twitterCollection.fetch({
      success: function () {
        $('#twitterList').html(twitterListView.render().el);
      }
    });

  }

});
var twitterRouter = new TwitterRouter();
Backbone.history.start();