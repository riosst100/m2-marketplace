<?php 
namespace Ves\Trackorder\Model\Mail;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;


class UploadTransportBuilder extends TransportBuilder {  
     /**
     * Template data
     *
     * @var array
     */
     protected $templateData = [];
    /**
     * Set template data
     *
     * @param array $data
     * @return $this
     */
    public function setTemplateData($data)
    {
        $this->templateData = $data;
        return $this;
    }
    public function addAttachmentFile($file, $name, $file_type = "application/pdf") {   
        if (!empty($file) && file_exists($file)) { 
            $this->message
            ->createAttachment(
                file_get_contents($file),
                $file_type,
                \Zend_Mime::DISPOSITION_ATTACHMENT,
                \Zend_Mime::ENCODING_BASE64,
                basename($name)
            );
        }

        return $this;
    }
} 