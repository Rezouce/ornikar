<?php declare(strict_types=1);

namespace Test;

use App\Context\ApplicationContext;
use App\Entity\Instructor;
use App\Entity\Learner;
use App\Entity\Lesson;
use App\Entity\MeetingPoint;
use App\Entity\Template;
use App\Repository\InstructorRepository;
use App\Repository\MeetingPointRepository;
use App\TemplateManager;
use DateTime;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class TemplateManagerTest extends TestCase
{
    public function test_it_replaces_the_placeholders_by_the_lesson_data(): void
    {
        $lesson = $this->createLesson(
            new MeetingPoint(1, "http://lambda.to", "paris 5eme"),
            new Instructor(1, "jean", "rock"),
            '2021-01-01 12:00:00',
            '2021-01-01 13:00:00',
        );

        $template = new Template(
            1,
            'Votre leçon de conduite avec [lesson:instructor_name]',
            <<<END
            Bonjour [user:first_name],
            
            La reservation du [lesson:start_date] de [lesson:start_time] à [lesson:end_time] avec [lesson:instructor_name] a bien été prise en compte!
            Voici votre point de rendez-vous: [lesson:meeting_point].
            
            Bien cordialement,
            
            L'équipe Ornikar
            END
        );

        $message = (new TemplateManager(ApplicationContext::getInstance()))
            ->addDependency(MeetingPointRepository::getInstance())
            ->addDependency(InstructorRepository::getInstance())
            ->getTemplateComputed($template, ['lesson' => $lesson]);

        $this->assertEquals('Votre leçon de conduite avec jean', $message->subject);
        $this->assertEquals(
            <<<END
            Bonjour Toto,
            
            La reservation du 01/01/2021 de 12:00 à 13:00 avec jean a bien été prise en compte!
            Voici votre point de rendez-vous: paris 5eme.
            
            Bien cordialement,
            
            L'équipe Ornikar
            END,
            $message->content
        );
    }

    public function test_it_will_throw_an_exception_if_a_dependency_is_missing(): void
    {
        $lesson = $this->createLesson(
            new MeetingPoint(1, "http://lambda.to", "paris 5eme"),
            new Instructor(1, "jean", "rock"),
            '2021-01-01 12:00:00',
            '2021-01-01 13:00:00',
        );

        $template = new Template(1, '', '[lesson:meeting_point]');

        try {
            (new TemplateManager(ApplicationContext::getInstance()))
                ->getTemplateComputed($template, ['lesson' => $lesson]);

            self::fail(sprintf('The %s should be missing', MeetingPointRepository::class));
        } catch (RuntimeException $e) {
            self::assertEquals(
                sprintf('The TemplateManager is missing the dependency %s', MeetingPointRepository::class),
                $e->getMessage()
            );
        }
    }

    public function setUp(): void
    {
        parent::setUp();

        ApplicationContext::getInstance()
            ->setCurrentUser(new Learner(1, "toto", "bob", "toto@bob.to"));
    }

    private function createLesson(
        MeetingPoint $meetingPoint,
        Instructor $instructor,
        string $startAt,
        string $endAt
    ): Lesson {
        MeetingPointRepository::getInstance()->save($meetingPoint);
        InstructorRepository::getInstance()->save($instructor);

        return new Lesson(1, $meetingPoint->id, $instructor->id, new DateTime($startAt), new DateTime($endAt));
    }
}
