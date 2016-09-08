<?hh //strict

namespace LayoutCore\Helper;

use Plenty\Plugin\Application;

class AbstractFactory
{
    private Application $app;

    public function __construct( Application $app )
    {
        $this->app = $app;
    }

    public function make<T>( classname<T> $className ):T
    {
        $instance = $this->app->make( $className );
        invariant( $instance instanceof $className, "Cannot create instance of class: " . $className );
        return $instance;
    }
}
