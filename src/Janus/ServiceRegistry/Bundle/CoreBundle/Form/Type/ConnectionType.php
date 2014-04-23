<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Bundle\CoreBundle\Form\Type;

use Janus\ServiceRegistry\Connection\ConnectionDto;
use Janus\ServiceRegistry\Entity\Connection;

use Janus\ServiceRegistry\Connection\Metadata\ConfigFieldsParser;
use Janus\ServiceRegistry\Bundle\CoreBundle\Form\DataTransformer\Connection\MetadataToNestedCollectionTransformer;
use Janus\ServiceRegistry\Bundle\CoreBundle\Form\Type\Connection\MetadataType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormConfigBuilder;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConnectionType extends AbstractType
{
    /** @var \Janus\ServiceRegistry\Connection\Metadata\ConfigFieldsParser */
    protected $configFieldsParser;

    /** @var  \SimpleSAML_Configuration */
    protected $janusConfig;

    /**
     * @param \SimpleSAML_Configuration $janusConfig
     */
    public function __construct(\SimpleSAML_Configuration $janusConfig)
    {
        $this->janusConfig = $janusConfig;
        $this->configFieldsParser = new ConfigFieldsParser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text');
        $builder->add('state', 'choice', array(
            'choices' => array(
                'testaccepted' => 'Test Accepted',
                'prodaccepted' => 'Prod Accepted'
            )
        ));
        $builder->add('type', 'choice', array(
            'choices' => array(
                Connection::TYPE_IDP => 'SAML 2.0 Idp',
                Connection::TYPE_SP => 'SAML 2.0 Sp'
            ),
            'disabled' => true,
        ));
        $this->enableTypeFieldOnNewConnection($builder);

        $builder->add('expirationDate', 'datetime', array(
            'required' => false
        ));
        $builder->add('metadataUrl', 'text', array(
            'required' => false
        ));
        $builder->add('metadataValidUntil', 'datetime', array(
            'required' => false
        ));
        $builder->add('metadataCacheUntil', 'datetime', array(
            'required' => false
        ));
        $builder->add('allowAllEntities', 'checkbox');
        $builder->add('arpAttributes', 'textarea', array(
            'required' => false
        ));
        $builder->add('manipulationCode', 'textarea', array(
            'required' => false
        ));
        $builder->add('revisionNote', 'textarea');
        $builder->add('notes', 'textarea', array(
            'required' => false
        ));
        $builder->add('isActive', 'checkbox');

        $builder->add('allowedConnections'  , 'collection', array(
            'type' => new ConnectionReferenceType(),
            'allow_add' => true,
        ));
        $builder->add('blockedConnections'  , 'collection', array(
            'type' => new ConnectionReferenceType(),
            'allow_add' => true,
        ));
        $builder->add('disableConsentConnections', 'collection', array(
            'type' => new ConnectionReferenceType(),
            'allow_add' => true,
        ));

        // Ignore these fields:
        $builder->add('createdAtDate'       , 'hidden', array('mapped' => false));
        $builder->add('updatedAtDate'       , 'hidden', array('mapped' => false));
        $builder->add('id'                  , 'hidden', array('mapped' => false));
        $builder->add('revisionNr'          , 'hidden', array('mapped' => false));
        $builder->add('updatedByUserName'   , 'hidden', array('mapped' => false));
        $builder->add('updatedFromIp'       , 'hidden', array('mapped' => false));
        $builder->add('parentRevisionNr'    , 'hidden');

        /** @var ConnectionDto $data */

        if (!isset($options['data'])) {
            throw new \RuntimeException(
                "No data set"
            );
        }
        $data = $options['data'];
        if (!$data->getType()) {
            throw new \RuntimeException(
                'No "type" in input! I need a type to detect which metadatafields should be required.'
            );
        }
        $this->addMetadataFields($builder, $this->janusConfig, $data->getType(), $options);
    }

    /**
     * Adds metadata field with type dependant config
     *
     * @param FormBuilderInterface $builder
     * @param \SimpleSAML_Configuration $janusConfig
     * @param $connectionType
     */
    protected function addMetadataFields(
        FormBuilderInterface $builder,
        \SimpleSAML_Configuration $janusConfig,
        $connectionType,
        $options
    ) {
        $metadataFieldsConfig = $this->getMetadataFieldsConfig($janusConfig, $connectionType);

        $metadataFormTypeOptions = array();
        if (isset($options['csrf_protection'])) {
            $metadataFormTypeOptions['csrf_protection'] = $options['csrf_protection'];
        }
        $builder->add(
            $builder->create('metadata', new MetadataType($metadataFieldsConfig), $metadataFormTypeOptions)
                ->addModelTransformer(new MetadataToNestedCollectionTransformer($connectionType, $janusConfig))
        );
    }

    /**
     * @param \SimpleSAML_Configuration $janusConfig
     * @param $connectionType
     * @return array
     */
    protected function getMetadataFieldsConfig(\SimpleSAML_Configuration $janusConfig, $connectionType)
    {
        // Get the configuration for the metadata fields from the Janus configuration
        $janusMetadataFieldsConfig = $this->findJanusMetadataConfig($janusConfig, $connectionType);

        // Convert it to hierarchical structure that we can use to build a form.
        $metadataFieldsConfig = $this->configFieldsParser->parse($janusMetadataFieldsConfig)->getChildren();
        return $metadataFieldsConfig;
    }

    /**
     * @param \SimpleSAML_Configuration $janusConfig
     * @param $connectionType
     * @return mixed
     * @throws \Exception
     */
    protected function findJanusMetadataConfig(\SimpleSAML_Configuration $janusConfig, $connectionType)
    {
        $configKey = "metadatafields.{$connectionType}";
        if (!$janusConfig->hasValue($configKey)) {
            throw new \Exception("No metadatafields config found for type {$connectionType}");
        }

        $metadataFieldsConfig = $janusConfig->getArray($configKey);
        return $metadataFieldsConfig;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => '\Janus\ServiceRegistry\Connection\ConnectionDto',
            'intention' => 'connection',
            'translation_domain' => 'JanusServiceRegistryBundle',
            'extra_fields_message' => 'This form should not contain these extra fields: "{{ extra_fields }}"',
        ));
    }

    public function getName()
    {
        return null;
    }

    /**
     * @param FormBuilderInterface $builder
     */
    protected function enableTypeFieldOnNewConnection(FormBuilderInterface $builder)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $connection = $event->getData();
            $form = $event->getForm();

            // Check if the Connection object is already defined, if so we may not allow the user to modify the type.
            if ($connection && $connection->getId() !== null) {
                return;
            }

            $typeConfig = $form->get('type')->getConfig();
            if (!$typeConfig instanceof FormConfigBuilder) {
                throw new \RuntimeException('Form type "type" has a unrecognized Configuration type');
            }

            $typeConfig->setAttribute('disabled', false);
        });
    }
}
