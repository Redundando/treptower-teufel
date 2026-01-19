(() => {
    const el = wp.element.createElement;
    const { __ } = wp.i18n;
    const { registerBlockType } = wp.blocks;
    const { InspectorControls, useBlockProps } = wp.blockEditor;
    const {
        PanelBody,
        TextControl,
        ToggleControl,
        RangeControl,
        Notice,
        Disabled
    } = wp.components;

    const ServerSideRender = wp.serverSideRender;

    registerBlockType("klohn-kit/calendar", {
        title: __("KK Calendar", "klohn-kit"),
        icon: "calendar-alt",
        category: "widgets",
        description: __("Render a calendar from an ICS URL (server-side).", "klohn-kit"),

        attributes: {
            icsUrl: { type: "string", default: "" },
            start: { type: "string", default: "" },
            end: { type: "string", default: "" },
            max: { type: "number", default: 999 },
            groupByMonth: { type: "boolean", default: true },
            cacheMinutes: { type: "number", default: 0 },
            linkText: { type: "string", default: "» Details" }
        },

        supports: {
            html: false,
            align: true
        },

        edit: (props) => {
            const { attributes, setAttributes } = props;
            const { icsUrl, start, end, max, groupByMonth, cacheMinutes, linkText } = attributes;

            const blockProps = useBlockProps({ className: "kk-calendar-block" });

            const inspector = el(
                InspectorControls,
                {},
                el(
                    PanelBody,
                    { title: __("Calendar", "klohn-kit"), initialOpen: true },

                    el(TextControl, {
                        label: __("ICS URL", "klohn-kit"),
                        value: icsUrl,
                        onChange: (v) => setAttributes({ icsUrl: v }),
                        placeholder: "https://calendar.google.com/calendar/ical/.../public/basic.ics"
                    }),

                    el(TextControl, {
                        label: __("Start (YYYY-MM-DD)", "klohn-kit"),
                        value: start,
                        onChange: (v) => setAttributes({ start: v }),
                        placeholder: "2026-01-01"
                    }),

                    el(TextControl, {
                        label: __("End (YYYY-MM-DD)", "klohn-kit"),
                        value: end,
                        onChange: (v) => setAttributes({ end: v }),
                        placeholder: "2026-12-31"
                    }),

                    el(RangeControl, {
                        label: __("Max events", "klohn-kit"),
                        value: max,
                        onChange: (v) => setAttributes({ max: v }),
                        min: 1,
                        max: 2000
                    }),

                    el(ToggleControl, {
                        label: __("Group by month", "klohn-kit"),
                        checked: !!groupByMonth,
                        onChange: (v) => setAttributes({ groupByMonth: !!v })
                    }),

                    el(RangeControl, {
                        label: __("Cache minutes (0 = no cache)", "klohn-kit"),
                        value: cacheMinutes,
                        onChange: (v) => setAttributes({ cacheMinutes: v }),
                        min: 0,
                        max: 1440
                    }),

                    el(TextControl, {
                        label: __("Link text", "klohn-kit"),
                        value: linkText,
                        onChange: (v) => setAttributes({ linkText: v })
                    })
                )
            );

            const preview = icsUrl
                ? el(
                    "div",
                    {
                        style: {
                            border: "1px dashed rgba(0,0,0,.15)",
                            padding: "10px",
                            borderRadius: "8px"
                        }
                    },
                    el(
                        Disabled,
                        {},
                        el(ServerSideRender, {
                            block: "klohn-kit/calendar",
                            attributes
                        })
                    )
                )
                : el(
                    Notice,
                    { status: "warning", isDismissible: false },
                    __("Set an ICS URL to preview the calendar.", "klohn-kit")
                );

            return el("div", blockProps, inspector, preview);
        },

        save: () => null
    });
})();
