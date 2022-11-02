BX.ready(function() {
    BX.namespace("BX.UI");


    if(BX.UI.EntityEditorSection !== undefined && BX.UI.EntityEditorSection.prototype.layout !== undefined && BX.UI.EntityEditorControl !== undefined)
    {
        //Avivi: set mode function
        BX.UI.EntityEditorControl.prototype.setMode = function(mode, options)
        {
            if(!this.canChangeMode(mode))
            {
                return;
            }

            var modeOptions = BX.prop.getInteger(options, "options", BX.UI.EntityEditorModeOptions.none);
            if(this._mode === mode && this._modeOptions === modeOptions)
            {
                return;
            }

            this.onBeforeModeChange();

            this._mode = mode;
            this._modeOptions = modeOptions;
            this.doSetMode(this._mode);

            this.onAfterModeChange();

            if(BX.prop.getBoolean(options, "notify", false))
            {
                if(this._parent)
                {
                    this._parent.processChildControlModeChange(this);
                }
                else if(this._editor)
                {
                    this._editor.processControlModeChange(this);
                }
            }

            this._isSchemeChanged = false;
            this._isChanged = false;

            if(this._hasLayout)
            {
                this._isValidLayout = false;
            }

            BX.onCustomEvent('onAfterSetMode', [mode]);//Avivi: add custom event
        }
        //Avivi: set mode function - end

        //Avivi: open click function
        BX.UI.EntityEditorSection.prototype.onOpenClick = function (e)
        {
            if (this._openButton.classList.contains('closed'))
            {
                this._contentContainer.style.display = "block";
                this._toggleButton.style.display = "block";
                window.openContainers.push(this._contentContainer.getAttribute('data-name'));
                this._openButton.classList.remove('closed');
                this._openButton.innerHTML = 'Close';
            }
            else
                {
                    this._contentContainer.style.display = "none";
                    this._toggleButton.style.display = "none";
                    var index = window.openContainers.indexOf(this._contentContainer.getAttribute('data-name'));
                    window.openContainers.splice(index, 1);
                    this._openButton.classList.add('closed');
                    this._openButton.innerHTML = 'Open';

                }
        };
        //Avivi: finish code

        BX.UI.EntityEditorSection.prototype.layout = function (options)
        {
            //Create wrapper
            var title = this._schemeElement.getTitle();
            this._contentContainer = BX.create("div", {props: {className: "ui-entity-editor-section-content"}});
            var isViewMode = this._mode === BX.UI.EntityEditorMode.view;

            //Avivi: if view mode hide content
            if(options.userFieldLoader !== undefined)
                if(options.userFieldLoader._owner._entityTypeName == "DEAL") {
                    if (window.openContainers == undefined) {
                        window.openContainers = [];
                    }
                    if (isViewMode && !window.openContainers.includes(this._schemeElement._settings.name)) {
                        if(this._id !== 'main') {
                            this._contentContainer.style.display = "none";
                        }
                    }
                }
            //Avivi: finish code

            var wrapperClassName = isViewMode
                ? "ui-entity-editor-section"
                : "ui-entity-editor-section-edit";

            this._enableToggling = this.isModeToggleEnabled() && this._schemeElement.getDataBooleanParam("enableToggling", true);

            //Avivi: add open button
            if(options.userFieldLoader !== undefined)
                if(options.userFieldLoader._owner._entityTypeName == "DEAL") {
                    if(this._id !== 'main') {
                        if (!window.openContainers.includes(this._schemeElement._settings.name)) {
                            var open_button_class = "open_button closed";
                        } else {
                            var open_button_class = "open_button";
                        }

                        this._openButton = BX.create("span",
                            {
                                attrs: {className: open_button_class},
                                events: {click: BX.delegate(this.onOpenClick, this)},
                                text: "Open"
                            }
                        );
                    }
                }
            //Avivi: finish code

            this._toggleButton = BX.create("span",
                {
                    attrs: {className: "ui-entity-editor-header-edit-lnk"},
                    events: {click: BX.delegate(this.onToggleBtnClick, this)},
                    text: BX.message(isViewMode ? "UI_ENTITY_EDITOR_CHANGE" : "UI_ENTITY_EDITOR_CANCEL")
                }
            );

            //Avivi: hide change button
            if(options.userFieldLoader !== undefined)
                if(options.userFieldLoader._owner._entityTypeName == "DEAL") {
                    if (!window.openContainers.includes(this._schemeElement._settings.name)) {
                        this._toggleButton.style.display = "none";
                    }
                }
            //Avivi: finish code

            if (!this._enableToggling) {
                this._toggleButton.style.display = "none";
            }

            var firstColumn = this.getEditor().getControlByIndex(0);
            var url = BX.prop.getString(this.getEditor()._settings, "entityDetailsUrl", "");
            if (this.getEditor().isEmbedded() && url.length) {
                var sectionIndex = null;
                if (firstColumn) {
                    sectionIndex = firstColumn.getChildren().indexOf(this);
                }

                if (sectionIndex === 0) {
                    this._detailButton = BX.create("a",
                        {
                            attrs: {
                                className: "ui-entity-editor-detail-btn",
                                href: url
                            },
                            text: BX.message('UI_ENTITY_EDITOR_SECTION_OPEN_DETAILS')
                        }
                    );
                }
            }

            this._titleMode = BX.UI.EntityEditorMode.view;

            this._wrapper = wrapper = BX.create("div", {props: {className: wrapperClassName}});

            if (this._schemeElement.isTitleEnabled()) {

                this._headerContainer = BX.create('div',
                    {
                        props: {className: 'ui-entity-editor-section-header'}
                    });

                if (this.isDragEnabled()) {
                    this._headerContainer.appendChild(this.createDragButton());
                }

                this._titleEditButton = BX.create("span",
                    {
                        props: {className: "ui-entity-editor-header-title-edit-icon"},
                        events: {click: this._titleEditHandler}
                    });

                if (!this._editor.isSectionEditEnabled() || !this._editor.canChangeScheme()) {
                    this._titleEditButton.style.display = "none";
                }

                this._titleView = BX.create("span",
                    {
                        props: {className: "ui-entity-editor-header-title-text"},
                        text: title
                    });

                this._titleInput = BX.create("input",
                    {
                        props: {className: "ui-entity-editor-header-title-text"},
                        style: {display: "none"}
                    });

                this._titleActions = BX.create("div",
                    {
                        props: {className: "ui-entity-editor-header-actions"},
                        children: [this._toggleButton, this._openButton]
                    });

                if (this._detailButton) {
                    this._titleActions.appendChild(this._detailButton);
                }

                this._titleContainer = BX.create("div",
                    {
                        props: {className: "ui-entity-editor-header-title"},
                        children:
                            [
                                this._titleView,
                                this._titleInput,
                                this._titleEditButton
                            ]
                    });

                this._headerContainer.appendChild(this._titleContainer);
                this._headerContainer.appendChild(this._titleActions);


                this._wrapper.appendChild(this._headerContainer);
            }


            this._wrapper.appendChild(this._contentContainer);

            if (!BX.type.isPlainObject(options)) {
                options = {};
            }

            var anchor = BX.prop.getElementNode(options, "anchor", null);
            if (anchor) {
                this._container.insertBefore(this._wrapper, anchor);
            } else {
                this._container.appendChild(this._wrapper);
            }

            if (isViewMode && this._fields.length === 0) {
                this._stub = BX.UI.EntityEditorSectionContentStub.create(
                    {owner: this, container: this._contentContainer}
                );
                this._stub.layout();
            }

            var enableReset = BX.prop.getBoolean(options, "reset", false);
            //Layout fields
            var userFieldLoader = BX.prop.get(options, "userFieldLoader", null);
            if (!userFieldLoader) {
                userFieldLoader = BX.UI.EntityUserFieldLayoutLoader.create(
                    this._id,
                    {mode: this._mode, enableBatchMode: true, owner: this}
                );
            }

            var enableFocusGain = BX.prop.getBoolean(options, "enableFocusGain", true);
            var lighting = BX.prop.getObject(options, "lighting", null);
            var isLighted = false;
            var isFieldContextMenuEnabled = false;
            for (var i = 0, l = this._fields.length; i < l; i++) {
                var field = this._fields[i];

                field.setContainer(this._contentContainer);
                field.setDraggableContextId(this._draggableContextId);

                //Force layout reset because of animation implementation
                field.releaseLayout();
                if (enableReset) {
                    field.reset();
                }

                var layoutOptions = {userFieldLoader: userFieldLoader};
                if (!isLighted && lighting && field.isVisible() && field.isNeedToDisplay()) {
                    layoutOptions["lighting"] = lighting;
                    isLighted = true;
                }

                field.layout(layoutOptions);
                if (enableFocusGain && !isViewMode && field.isHeading()) {
                    field.focus();
                }

                if (!isFieldContextMenuEnabled && field.isContextMenuEnabled()) {
                    isFieldContextMenuEnabled = true;
                }
            }

            if (isFieldContextMenuEnabled) {
                BX.addClass(this._contentContainer, "ui-entity-editor-section-content-padding-right");
            }

            if (userFieldLoader.getOwner() === this) {
                userFieldLoader.runBatch();
            }

            this._addChildButton = this._createChildButton = this._deleteButton = null;

            if (this._editor.canChangeScheme() && this._schemeElement.getDataBooleanParam("showButtonPanel", true)) {
                this.showButtonPanel();
            }

            if (this.isDragEnabled()) {
                this._dragContainerController = BX.UI.EditorDragContainerController.create(
                    "section_" + this.getId(),
                    {
                        charge: BX.UI.EditorFieldDragContainer.create(
                            {
                                section: this,
                                context: this._draggableContextId
                            }
                        ),
                        node: this._wrapper
                    }
                );
                this._dragContainerController.addDragFinishListener(this._dropHandler);

                this.initializeDragDropAbilities();
            }

            //region Add custom Html
            var serialNumber = null;
            if (firstColumn) {
                serialNumber = firstColumn.getChildren().indexOf(this);
            }
            var eventArgs = {id: this._id, customNodes: [], visible: true, serialNumber: serialNumber};
            BX.onCustomEvent(window, this.eventsNamespace + ":onLayout", [this, eventArgs]);
            if (this._titleActions && BX.type.isArray(eventArgs["customNodes"])) {
                for (var j = 0, length = eventArgs["customNodes"].length; j < length; j++) {
                    var node = eventArgs["customNodes"][j];
                    if (BX.type.isElementNode(node)) {
                        this._titleActions.appendChild(node);
                    }
                }
            }
            if ("visible" in eventArgs && BX.type.isBoolean(eventArgs["visible"])) {
                this.setVisible(eventArgs["visible"]);
            }
            //endregion

            this.registerLayout(options);
            this._hasLayout = true;
        };

        BX.UI.EntityEditorSection.prototype.onToggleBtnClick = function(e)
        {
            this.toggle();

            //Avivi: Moving open_button after AI Scoring
            if(document.querySelector('.crm-entity-widget-scoring') && document.querySelector('.open_button'))
            {
                document.getElementsByClassName('crm-entity-widget-scoring')[0].after(document.querySelector('.open_button'));
            }
            if($('.ui-entity-editor-content-block[data-cid="CLIENT"] ' +
                    '.crm-entity-widget-content-block-field-container-inner').length)
            {
                $('.ui-entity-editor-content-block[data-cid="CLIENT"] .crm-entity-widget-content-block-field-container-inner').attr('style', 'display: flex !important; flex-direction: column !important;');

               $('.ui-entity-editor-content-block[data-cid="CLIENT"] ' +
                '.crm-entity-widget-content-block-field-container-inner ' +
                '.crm-entity-widget-content-inner-row').map(function (){
                    if($(this).find('.crm-entity-widget-content-block-title .crm-entity-widget-content-block-title-text').html() == 'Company')
                    {
                        $(this).css('order','1');
                    }
                    else
                        if($(this).find('.crm-entity-widget-content-block-title .crm-entity-widget-content-block-title-text').html() == 'Contact')
                        {
                            $(this).css('order','2');
                        }
               });
            }
            //Avivi: finish code
        };

        BX.UI.EntityEditorToolPanel.prototype.onSaveButtonClick = function(e)
        {
            if(!this._isLocked)
            {
                this._editor.saveChanged();
            }

            //Avivi: Moving open_button after AI Scoring
            if(document.querySelector('.crm-entity-widget-scoring') && document.querySelector('.open_button'))
            {
                document.getElementsByClassName('crm-entity-widget-scoring')[0].after(document.querySelector('.open_button'));
            }
            //Avivi: finish code
        };

        BX.UI.EntityEditorToolPanel.prototype.onCancelButtonClick = function(e)
        {
            if(!this._isLocked)
            {
                this._editor.cancel();
            }

            //Avivi: Moving open_button after AI Scoring
            if(document.querySelector('.crm-entity-widget-scoring') && document.querySelector('.open_button'))
            {
                document.getElementsByClassName('crm-entity-widget-scoring')[0].after(document.querySelector('.open_button'));
            }
            //Avivi: finish code

            return BX.eventReturnFalse(e);
        };
    }
});