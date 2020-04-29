<?php

declare(strict_types=1);

namespace PantherExtension\Context;

use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use PantherExtension\Driver\Exception\LogicException;
use PantherExtension\Driver\PantherDriver;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
final class PantherContext extends RawMinkContext
{
    /**
     * @AfterScenarioScope
     */
    public static function reset(AfterScenarioScope $scope): void
    {
        $environment = $scope->getEnvironment();

        if (!$environment instanceof InitializedContextEnvironment) {
            return;
        }

        $context = $environment->getContext(PantherContext::class);
        $driver = $context->getDriver();

        if (!$driver instanceof PantherDriver) {
            throw new LogicException(
                sprintf('The driver should be an instance of "%s", found "%s".', PantherDriver::class, get_class($driver))
            );
        }

        $driver->resetClients();
    }

    /**
     * @Given I wait for :element
     * @And   I wait for :element
     */
    public function iWaitForElement(string $element): void
    {
        $driver = $this->getDriver();

        try {
            $driver->waitFor($element);
        } catch (TimeoutException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found in the given timeout or seems to appear later than expected.'));
        } catch (NoSuchElementException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found.'));
        }
    }

    /**
     * @Given I wait for :element during :timeout
     * @And   I wait for :element during :timeout
     */
    public function iWaitForElementDuring(string $element, int $timeoutInSeconds = 30): void
    {
        $driver = $this->getDriver();

        try {
            $driver->waitFor($element, $timeoutInSeconds);
        } catch (TimeoutException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found in the given timeout or seems to appear later than expected.'));
        } catch (NoSuchElementException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found.'));
        }
    }

    /**
     * @Given I wait for :element during :timeout every :interval
     * @And   I wait for :element during :timeout every :interval
     */
    public function iWaitForElementDuringEvery(string $element, int $timeoutInSeconds = 30, int $intervalMilliseconds = 250): void
    {
        $driver = $this->getDriver();

        try {
            $driver->waitFor($element, $timeoutInSeconds, $intervalMilliseconds);
        } catch (TimeoutException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found in the given timeout or seems to appear later than expected.'));
        } catch (NoSuchElementException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found.'));
        }
    }

    /**
     * @Given I create a new client :name using the :driver driver
     * @And   I create a new client :name using the :driver driver
     */
    public function iCreateANewClient(string $name, string $driver): void
    {
        $this->getDriver()->createAdditionalClient($name, $driver);
    }

    /**
     * @Given I create a new set of clients :options
     * @And   I create a new set of clients :options
     */
    public function iCreateANewSetOfClientUsingTheDriveAndTheFollowingOptions(TableNode $options): void
    {
        foreach ($options as $option) {
            $this->getDriver()->createAdditionalClient(
                $option['name'],
                $option['driver'],
                explode(', ', $option['options'] ?? '') ?? []
            );
        }
    }

    /**
     * @Given I switch to client :name
     * @And   I switch to client :name
     */
    public function iSwitchToANewClient(string $name): void
    {
        $this->getDriver()->switchToClient($name);
    }

    /**
     * @Given I switch back to the default client
     * @And   I switch back to the default client
     */
    public function iSwitchBackToDefaultClient(): void
    {
        $this->getDriver()->switchToClient(PantherDriver::DEFAULT_CLIENT_KEY);
    }

    /**
     * @Given I remove the client :name
     * @And   I remove the client :name
     */
    public function iRemoveTheClient(string $name): void
    {
        $this->getDriver()->removeClient($name);
    }

    /**
     * @Given I should have :count clients
     * @And   I should have :count clients
     */
    public function iShouldHave(int $count): void
    {
        $driver = $this->getDriver();

        if ($count !== $clientCount = \count($driver->getClients())) {
            throw new LogicException(
                sprintf('The desired client count cannot be validated, found "%d".', $clientCount)
            );
        }
    }

    /**
     * @Given I switch to fullscreen
     * @And   I switch to fullscreen
     */
    public function iSwitchToFullScreen(): void
    {
        $this->getDriver()->moveToFullScreen();
    }

    /**
     * @Given I change the screen orientation to :orientation
     * @And   I change the screen orientation to :orientation
     */
    public function iChangeTheScreenOrientation(string $orientation = 'PORTRAIT'): void
    {
        $this->getDriver()->setOrientation($orientation);
    }

    /**
     * @Given I scroll to :xOffset :yOffset
     * @And   I scroll to :xOffset :yOffset
     */
    public function iScrollTo(int $xOffset, int $yOffset): void
    {
        $this->getDriver()->scrollTo($xOffset, $yOffset);
    }

    /**
     * @Given I scroll from :element to :xOffset :yOffset
     * @And   I scroll from :element to :xOffset :yOffset
     */
    public function iScrollFromTo(string $element, int $xOffset, int $yOffset): void
    {
        $this->getDriver()->scrollFromTo($element, $xOffset, $yOffset);
    }

    /**
     * @Given If the :capability browser capability is enabled
     * @And   If the :capability browser capability is enabled
     */
    public function ifTheBrowserCapabilityIsEnabled(string $capability): void
    {
        $this->getDriver()->isCapabilityAvailable($capability);
    }

    /**
     * @Given I try to set a new browser capability called :capability with the value :value
     * @And   I try to set a new browser capability called :capability with the value :value
     */
    public function iTryToSetANewBrowserCapabilityWithTheValue(string $capability, string $value): void
    {
        $this->getDriver()->setCapability($capability, $value);
    }

    /**
     * @Given I take a new screenshot
     * @And   I take a new screenshot
     */
    public function iTakeANewScreenshot(): void
    {
        $this->getDriver()->takeScreenshot($this->getMinkParameter('files_path'));
    }

    private function getDriver(): PantherDriver
    {
        $driver = $this->getSession()->getDriver();

        if (!$driver instanceof PantherDriver) {
            throw new LogicException(
                sprintf('The driver must be %s in order to wait for element | Ajax requests.', get_class($driver))
            );
        }

        return $driver;
    }
}
