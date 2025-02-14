<?php

namespace Clue\React\Soap\Protocol;

use Psr\Http\Message\RequestInterface;
use RingCentral\Psr7\Request;

/**
 * @internal
 */
final class ClientEncoder extends \SoapClient
{
    private $request = null;

    /**
     * Encodes the given RPC function name and arguments as a SOAP request
     *
     * @param string $name
     * @param array  $args
     * @return RequestInterface
     * @throws \SoapFault if request is invalid according to WSDL
     */
    public function encode(string $name, array $args): RequestInterface
    {
        $this->__soapCall($name, $args);

        $request = $this->request;
        $this->request = null;

        return $request;
    }

    /**
     * Overwrites the internal request logic to build the request message
     *
     * By overwriting this method, we can skip the actual request sending logic
     * and still use the internal request serializing logic by accessing the
     * given `$request` parameter and building our custom request object from
     * it. We skip/ignore its parsing logic by returing an empty response here.
     * This will implicitly be invoked by the call to `__soapCall()` in the
     * above `encode()` method.
     *
     * @see \SoapClient::__doRequest()
     */
    public function __doRequest(string $request, string $location, string $action, int $version, bool $one_way = false): ?string
    {
        $headers = array();
        if ($version === SOAP_1_1) {
            $headers = array(
                'SOAPAction' => $action,
                'Content-Type' => 'text/xml; charset=utf-8'
            );
        } elseif ($version === SOAP_1_2) {
            $headers = array(
                'Content-Type' => 'application/soap+xml; charset=utf-8; action=' . $action
            );
        }

        $this->request = new Request(
            'POST',
            (string)$location,
            $headers,
            (string)$request
        );

        // do not actually block here, just pretend we're done...
        return '';
    }
}
