<?php

namespace CharacterGeneratorDev {


/**
 * IXR_Base64
 *
 * @package IXR
 * @since 1.5.0
 */
class IXR_Base64
{
    var $data;

	/**
	 * PHP5 constructor.
	 */
    function CG_Dev\\__construct( $data )
    {
        $this->data = $data;
    }

	/**
	 * PHP4 constructor.
	 */
	public function IXR_Base64( $data ) {
		self::__construct( $data );
	}

    function CG_Dev\\getXml()
    {
        return '<base64>'.base64_encode($this->data).'</base64>';
    }
}

} // namespace CharacterGeneratorDev
