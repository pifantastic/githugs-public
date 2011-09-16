var path      = require('path'),
    express   = require('express'),
    jade      = require('jade'),
    GitHubApi = require("github").GitHubApi,
    GitHugs   = require(__dirname + '/lib/githugs').GitHugs,
    hugs      = require(__dirname + '/lib/hugs'),
    ago       = require(__dirname + '/lib/ago'),
    config    = require(__dirname + '/config');

var app = express.createServer(),
    hugger = new GitHugs(),
    github = new GitHubApi(true),
    hugs_given = [];

app.configure(function() {
  app.set('views', __dirname + '/views');
  app.set('view engine', 'jade');
  app.use(express.methodOverride());
  app.use(express.bodyParser());
  app.use(app.router);
});

app.configure('development', function() {
  app.use(express.static(__dirname + '/public'));
  app.use(express.errorHandler({ dumpExceptions: true, showStack: true }));
});

app.configure('production', function() {
  app.use(express.static(__dirname + '/public', { maxAge: 31557600000 }));
  app.use(express.errorHandler());
});

app.get('/', function(req, res){
  res.render('index', { title: 'githu.gs', hugs: hugs_given.reverse().slice(0, 20), ago: ago });
});
  
app.listen(config.server.port, config.server.host);
console.log("Waiting to give hugs on port %d", app.address().port);

var interval = setInterval(function loop() {
  // Get a list of users we're following.
  github.getUserApi().getFollowing(config.github.login, function(err, following) {
    if (!err && following.length) {
      // Pick one at random.
      var randomPerson = following[Math.floor(Math.random() * following.length)];
      // Get a list of their repos.
      github.getRepoApi().getUserRepos(randomPerson, function(err, repos) {
        if (!err && repos.length) {
          // Get the most recently changed repo.
          repos.sort(function(a, b) { return (new Date(b.pushed_at)) - (new Date(a.pushed_at)); });
          var repo = repos[0],
              hug = hugs.rand();
          // Hug'em.
          hugger.comment(repo.owner, repo.name, repo.master_branch || 'master', hug, function(err, commit) {
            var save = {
              hug: hug,
              url: 'https://github.com/' + repo.owner + '/' + repo.name,
              project: repo.owner + '/' + repo.name,
              time: new Date(),
              commit: commit
            };
            hugs_given.push(save);
          });
        } else {
          console.log("Couldn't find any repos for " + randomPerson + '.');
        }
      });
    } else {
      console.log("Couldn't figure out who " + config.github.login + " is following.");
    }
  });
}, config.hug.frequency);
