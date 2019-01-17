<?php declare(strict_types=1);

use LegalThings\DataEnricher;

/**
 * Instantiate an state from the state definition in the scenario.
 * @immutable
 */
class StateInstantiator
{
    /**
     * The data enricher evaluates data instructions.
     * @var DataEnricher
     */
    protected $dataEnricher;

    /**
     * Class constructor.
     *
     * @param DataEnricher  $dataEnricher
     */
    public function __construct(DataEnricher $dataEnricher)
    {
        $this->dataEnricher = $dataEnricher;
    }

    /**
     * Instantiate an state from the state definition.
     *
     * @param State   $definition
     * @param Process $process
     * @return State
     * @throws RuntimeException
     */
    public function instantiate(State $definition, Process $process): CurrentState
    {
        try {
            $state = clone $definition;
            $this->dataEnricher->applyTo($state, $process);
            $currentState = CurrentState::fromData($state->toData());

            $actionDefinitions = $process->scenario->getActionsForState($definition);
            $currentState->actions = $this->instantiateActions($actionDefinitions, $process);
        } catch (\Exception $e) {
            $err = "Failed to instantiate state" . (isset($definition->title) ? " '{$definition->title}'" : '');
            throw new \RuntimeException($err, 0, $e);
        }

        return $currentState;
    }

    /**
     * Instantiate actions from the scenario.
     *
     * @param iterable<Action> $actionDefinitions
     * @param Process          $process
     * @return AssocEntitySet&iterable<Action>
     */
    protected function instantiateActions(iterable $actionDefinition, Process $process): AssocEntitySet
    {
        $actions = AssocEntitySet::forClass(Action::class);

        foreach ($actionDefinition as $key => $definition) {
            $action = clone $definition;
            $this->dataEnricher->applyTo($action, $process);

            $actions[$key] = $action;
        }

        return $actions;
    }
}
