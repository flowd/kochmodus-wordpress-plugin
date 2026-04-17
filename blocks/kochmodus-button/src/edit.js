import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ColorPicker, BaseControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
    const { label, recipeUri, backgroundColor, hoverBackgroundColor, color } = attributes;
    const blockProps = useBlockProps();

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Button Settings', 'kochmodus')}>
                    <TextControl
                        label={__('Button Label', 'kochmodus')}
                        value={label}
                        onChange={(value) => setAttributes({ label: value })}
                        help={__('Default: "Kochmodus starten"', 'kochmodus')}
                    />
                    <TextControl
                        label={__('Recipe URI (optional)', 'kochmodus')}
                        value={recipeUri}
                        onChange={(value) => setAttributes({ recipeUri: value })}
                        help={__('Leave empty to use the current page URL.', 'kochmodus')}
                    />
                </PanelBody>
                <PanelBody title={__('Colors', 'kochmodus')} initialOpen={false}>
                    <BaseControl
                        label={__('Background Color', 'kochmodus')}
                        id="kochmodus-bg-color"
                    >
                        <ColorPicker
                            color={backgroundColor || '#3d7a5f'}
                            onChange={(value) => setAttributes({ backgroundColor: value })}
                            enableAlpha={false}
                        />
                        {backgroundColor && (
                            <button
                                type="button"
                                className="components-button is-link is-destructive"
                                onClick={() => setAttributes({ backgroundColor: '' })}
                                style={{ marginTop: '8px' }}
                            >
                                {__('Reset', 'kochmodus')}
                            </button>
                        )}
                    </BaseControl>
                    <BaseControl
                        label={__('Hover Background Color', 'kochmodus')}
                        id="kochmodus-hover-bg-color"
                    >
                        <ColorPicker
                            color={hoverBackgroundColor || '#316249'}
                            onChange={(value) => setAttributes({ hoverBackgroundColor: value })}
                            enableAlpha={false}
                        />
                        {hoverBackgroundColor && (
                            <button
                                type="button"
                                className="components-button is-link is-destructive"
                                onClick={() => setAttributes({ hoverBackgroundColor: '' })}
                                style={{ marginTop: '8px' }}
                            >
                                {__('Reset', 'kochmodus')}
                            </button>
                        )}
                    </BaseControl>
                    <BaseControl
                        label={__('Text Color', 'kochmodus')}
                        id="kochmodus-text-color"
                    >
                        <ColorPicker
                            color={color || '#ffffff'}
                            onChange={(value) => setAttributes({ color: value })}
                            enableAlpha={false}
                        />
                        {color && (
                            <button
                                type="button"
                                className="components-button is-link is-destructive"
                                onClick={() => setAttributes({ color: '' })}
                                style={{ marginTop: '8px' }}
                            >
                                {__('Reset', 'kochmodus')}
                            </button>
                        )}
                    </BaseControl>
                </PanelBody>
            </InspectorControls>
            <div {...blockProps}>
                <div className="kochmodus-button-preview">
                    <button
                        type="button"
                        disabled
                        className="kochmodus-preview-btn"
                        style={{
                            ...(backgroundColor ? { backgroundColor } : {}),
                            ...(color ? { color } : {}),
                        }}
                    >
                        {label || __('Kochmodus starten', 'kochmodus')}
                    </button>
                    {recipeUri && (
                        <small className="kochmodus-preview-uri">
                            {recipeUri}
                        </small>
                    )}
                </div>
            </div>
        </>
    );
}
