# Plugin doc building

Goal:

* Publish documentation for plugins on book.cakephp.org and make them appear
  as subprojects to the main documentation
* Build a common harness for plugins that requires minimal duplication in order
  to reduce maintenance costs.

Ideas:

Create a new repository that contains infrastructure for buiding plugin docs.
This repository would contain scripts that could be pointed at a plugins
documentation directory and run to generate a single plugin version's
documentation. Multiple versions of plugin docs will require building multiple
branches, and composing their output together.

Each plugin would need a deployDocs.Dockerfile which would:

- Be based off the harness repo docker file so we have nginx, sphinx
  php and curl installed.
- Pull in the harness repository
- For each version of the plugin
    - Replace the placeholders with relevant plugin name and version.
    - Use the harness repo to build each relevant branch.
    - Rebuild the relevant elasticsearch index
- Setup nginx to run.

The dokku management tooling would need updates for each plugin published this
way as each plugin needs nginx forwarding setup.
