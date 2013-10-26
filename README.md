![Hoa](http://static.hoa-project.net/Image/Hoa_small.png)

Hoa is a **modular**, **extensible** and **structured** set of PHP libraries.
Moreover, Hoa aims at being a bridge between industrial and research worlds.

# Hoathis\BenchBundle [![Build Status](https://travis-ci.org/jubianchi/HoathisBenchBundle.png?branch=master)](https://travis-ci.org/jubianchi/HoathisBenchBundle)

## Installation

Add these lines to your `require-dev` section:

```json
{
    "require-dev": {
        "hoathis/bench-bundle": "dev-master@dev"
    }
}
```

Then install dependencies:

```sh
$ composer update hoathis/bench-bundle
```

And add `BenchBundle` to your `AppKernel`:

```php
//app/AppKernel.php

class AppKernel extends Kernel
{
    …

    public function registerBundles()
    {
        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            …
            $bundles[] = new \Hoathis\Bundle\BenchBundle\BenchBundle();
        }

        return $bundles;
    }
}
```

## How to use

### Bench service

`BenchBundle` will automatically setup a `bench` service which you can use in your PHP code to benchmark parts of your application. Results will be aggregated and reported in the profile.

```php
<?php

namespace Hoathis\BenchDemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DemoController extends Controller
{
    public function indexAction()
    {
        $this->container->get('bench')->renderView->start();
        $response = $this->render('HoaDemoBundle:Welcome:index.html.twig');
        $this->container->get('bench')->renderView->stop();

        return $response;
    }
}
```

In the previous example we created a mark named `renderView` in measuring the time taken to render the Twig template.

You can create several marks by simply assigning them a unique name and nest them as you want :

```php
public function indexAction()
{
    $this->container->get('bench')->fetchUsers->start();
    $users = …
    
    foreach($users as $user) {
        $this->container->get('bench')->fetchMessages->start();
        $user->messages = …
        $this->container->get('bench')->fetchMessages->pause();
    }
    
    $this->container->get('bench')->fetchMessages->stop(true);
    $this->container->get('bench')->fetchUsers->stop();

    $this->container->get('bench')->renderView->start();
    $response = $this->render('HoaDemoBundle:Users:index.html.twig', array('users' => $users));
    $this->container->get('bench')->renderView->stop();

    return $response;
}
```

As you can see in the previous example you have three methods to control mark state:

* `Hoa\Bench\Mark::start()` : to start or unpause a mark
* `Hoa\Bench\Mark::pause($silent = false)` : to pause a mark
* `Hoa\Bench\Mark::stop($silent = false)` : to stop a mark

You can also get more informations from marks using their [native API](http://hoa-project.net/Literature/Hack/Bench.html#Bien_manipuler_les_marques).

### Twig helper

`BenchBundle` also adds a Twig helper to use marks inside your templates:

```html
<ul>
    {% benchstart 'usersLoop' %}
    {% for user in users %}
        <li>
            {{ user.username }}
            
            {% benchstart 'messagesCount' %}
            <span>
                {% if user.messages|length %}
                    No new mesages
                {% else %}
                   {{ user.messages|length }} new message(s)
                {% endif %}
            </span>
            {% benchpause 'messagesCount' %}
        <li>
    {% endfor %}
    {% benchstop 'messagesCount' %}
    {% benchstop 'usersLoop' %}
<ul>
```

Results of those marks will also be displayed in the web profiler.

### Console helper

Finally, `BenchBundle` will configure a `bench.helper` service which you can use in your console commands to access marks:

```php
<?php
namespace Hoa\DemoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends ContainerAwareCommand
{
    public function __construct($name = null)
    {
        parent::__construct($name ?: 'hoa:bench:demo');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bench = $this->getContainer()->get('bench.helper');

        $bench->start('foo');
        …

        $bench->start('bar');
        …
        $bench->stop('bar');

        $bench->stop('foo');

        $bench->summarize($output);
    }
} 
```

The API is the same as the `bench` service except that with the helper, you pass marks' names as argument of the `start`/`pause`/`stop` methods.

The results will be render on the command's output when you call the `summarize`method:

```sh
$ app/console hoa:bench:demo
# ...
+------+-----------------+-----------------+
| Mark | Time            | Percent         |
+------+-----------------+-----------------+
| foo  | 4.0034830570221 | 100             |
| bar  | 2.001620054245  | 49.996965785434 |
+------+-----------------+-----------------+
```
