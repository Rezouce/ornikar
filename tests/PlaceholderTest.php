<?php declare(strict_types=1);

namespace Test;

use App\Entity\Placeholder;
use PHPUnit\Framework\TestCase;

class PlaceholderTest extends TestCase
{
    public function test_it_will_return_the_placeholder_string_if_cast_as_a_string(): void
    {
        $placeholder = new Placeholder('object', 'property');

        self::assertEquals('[object:property]', (string)$placeholder);
    }

    public function test_it_can_return_the_getter_to_use_to_get_the_object_formatted_property(): void
    {
        $placeholder = new Placeholder('object', 'my_property');

        self::assertEquals('getMyProperty', $placeholder->getMethodName());
    }
}
