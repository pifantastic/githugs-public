var GitHubApi = require("github").GitHubApi,
    jsdom = require('jsdom'),
    https = require('https'),
    config = require(__dirname + '/../config');

var JQUERY = 'http://code.jquery.com/jquery-1.6.1.min.js';

var GitHugs = exports.GitHugs = function() {
  // Keep yo hands out.
  this.jar = [];
  this.api = new GitHubApi(true);
};

GitHugs.prototype = {
  // Login to github.
  login: function(login, password, callback) {
    var self = this;
    self.get('/login', {}, function(err, data) {
      // Grab the login form and extract the authenticity token.
      jsdom.env(data, [JQUERY], function(errors, window) {
        var post_data = {
          authenticity_token: window.$('input[name="authenticity_token"]').val(),
          login: login,
          password: password
        };
        
        self.post('/session', post_data, function(err, data) {
          callback(err, data);
        });
      });
    });
  },
  
  // Comment on the latest commit for a branch.
  comment: function(user, repo, branch, comment, callback) {
    var self = this;
    // Login to github.
    self.login(config.github.login, config.github.password, function(err, data) {
      // Find the most recent commit by this user.
      self.api.getCommitApi().getBranchCommits(user, repo, branch, function(err, commits) {
        if (!err && commits.length) {
          // Comment on the commit with a hapy message.
          self.get(commits[0].url, {}, function(err, data) {
            jsdom.env(data, [JQUERY], function(errors, window) {
              var form = window.$('#comments').next('form');
              form.find('textarea').html(comment);
              self.post(form.attr('action'), form.serialize(), function(err, data) {
                callback(err, commits[0]);
              });
            });
          })
        } else {
          callback(error, null);
        }
      });
    });
  },
  
  // Make a POST request to github.
  post: function(url, data, callback) {
    if (typeof data === 'object') {
      var body = [];
      for (var key in data) {
        body.push(key + '=' + encodeURIComponent(data[key]));
      }
      data = body.join('&');
    }
    this.request('POST', url, data, callback);
  },

  // Make a GET request to github.  
  get: function(url, data, callback) {
    var params = [];
    for (var key in data) {
      params.push(key + '=' + encodeURIComponent(data[key]));
    }
    this.request('GET', url + '?' + params.join('&'), false, callback);
  },
  
  // Make an HTTP request to github.
  request: function(type, url, data, callback) {
    var self = this,
        rxdata = '',
        options = {
          host: 'github.com',
          port: 443,
          path: url,
          method: type.toUpperCase(),
          headers: { 'Cookie': this.jar.join('; ') }
        };
    
    // Make an HTTPS request.
    var request = https.request(options, function(res) {
      if ('set-cookie' in res.headers) {
        self.jar = [];
        res.headers['set-cookie'].forEach(function(cookie) {
          var parts = cookie.split(/[;,] */);
          self.jar.push(parts[0].trim());
        });
      }
      
      // Accumulate response data.
      res.on('data', function(chunk) {
        rxdata += chunk;
      }).on('end', function() {
       callback(null, rxdata);
      });
    });
    
    request.on('error', function() {
      callback(true, null);
    });
    
    // Write POST body if necessary.
    if (data) {
      request.write(data);
    }
    
    request.end();
  }
};
