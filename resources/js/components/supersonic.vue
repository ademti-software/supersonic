<template>
    <div class="modal-container" v-show="isVisible">
        <div class="modal">
            <h2>{{ __('Supersonic') }}</h2>

            <!-- Main action pane -->
            <div v-if="mode === 'main'">
                <p>
                    <input type="text" id="supersonic-filter" v-model="filterVal">
                </p>
                <supersonic-action-list v-bind:actions="filteredActions"
                                        v-bind:selectedIdx="selectedIdx"
                                        @clickAction="clickAction">
                </supersonic-action-list>
                <supersonic-key-listener
                    v-bind:isVisible="isVisible"
                    @toggleVisibility="toggleVisibility()"
                    @visibilityHide="visibilityHide()"
                    @selectNext="mainSelectNext()"
                    @selectPrevious="mainSelectPrevious()"
                    @primaryAction="doAction('primary')"
                    @secondaryAction="doAction('secondary')"
                    @tertiaryAction="doAction('tertiary')"
                >
                </supersonic-key-listener>
            </div>
            <!-- Search pane -->
            <div v-if="mode === 'search'">
                <p>
                    <input type="text" id="supersonic-filter" v-model="searchVal">
                </p>
                <supersonic-action-list v-if="!isSearching && this.searchResults.length > 0" v-bind:actions="searchResults"
                                        v-bind:selectedIdx="selectedSearchIdx"
                                        @clickAction="clickAction">

                </supersonic-action-list>
                <div class="no-results-container" v-if="!isSearching && this.searchResults.length < 1">
                    <p v-if="this.errorSearching">
                        {{ __('There was an error fetching results. Sorry.') }}
                    </p>
                    <p v-if="!this.errorSearching && this.searchVal.length > 0">
                        {{ __('No results') }}
                    </p>
                    <p v-if="!this.errorSearching && this.searchVal.length < 1">
                        {{ __('Type to search in ') }}{{ this.filteredActions[selectedIdx].path + ' » ' + this.filteredActions[selectedIdx].name }}
                    </p>
                </div>
                <div class="spinner-container" v-if="isSearching">
                    <div>
                        <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
                        <p>
                            {{ __('Searching...') }}
                        </p>
                    </div>
                </div>
                <supersonic-key-listener
                    v-bind:isVisible="isVisible"
                    @toggleVisibility="toggleVisibility()"
                    @visibilityHide="visibilityHide()"
                    @selectNext="selectNextSearchResult()"
                    @selectPrevious="selectPreviousSearchResult()"
                    @primaryAction="doAction('primary')"
                    @secondaryAction="doAction('secondary')"
                    @tertiaryAction="doAction('tertiary')"
                    @backAction="leaveSearchAction()">
                </supersonic-key-listener>
            </div>
        </div>
    </div>
</template>

<script>
import throttle from "lodash.throttle";
import debounce from "lodash.debounce";

export default {
    props: {
        actions: {type: Object, required: true},
        isVisible: {type: Boolean, required: true},
        mode: {type: String, required: true},
    },
    data() {
        return {
            // Data required for main view.
            filterVal: '',
            filteredActions: {},
            selectedIdx: 0,
            // Data required for search view.
            searchVal: '',
            errorSearching: false,
            searchResults: [],
            selectedSearchIdx: 0,
            isSearching: false,
            searchUrl: '',
        };
    },
    mounted() {
        this.debouncedFilter = throttle(() => {
            this.filter();
        }, 100);
        this.filter();
        this.debouncedSearch = debounce(() => {
            this.search();
        }, 300);
    },
    beforeUnmount() {
        this.debouncedFilter.cancel();
    },
    watch: {
        filterVal() {
            this.debouncedFilter();
        },
        searchVal() {
            this.debouncedSearch();
        }
    },
    methods: {
        filter() {
            this.filteredActions = {};
            var lcFilterVal = this.filterVal.toLowerCase();
            var doneSelected = false;
            var matches = 0;
            this.selectedIdx = 0;
            for (var idx in this.actions) {
                if (this.actions[idx].searchName.includes(lcFilterVal)) {
                    this.filteredActions[matches] = Object.assign({
                        isSelected: !doneSelected,
                        ...this.actions[idx]
                    });
                    doneSelected = doneSelected || this.filteredActions[matches].isSelected;

                    matches++;
                }
            }
        },
        async search() {
            this.isSearching = true;
            this.errorSearching = false;
            this.searchResults = [];
            var lcSearchVal = this.searchVal.toLowerCase();

            const json = await fetch(this.searchUrl + '?' + new URLSearchParams({
                s: this.searchVal,
            })).then((response) => {
                this.selectedSearchIdx = 0;
                if (!response.ok) {
                    this.errorSearching = true;
                    return [];
                }

                return response.json();
            }).catch(() => {
                this.errorSearching = true;
                return [];
            });

            this.searchResults = await json;

            if (this.searchResults.length > 0) {
                this.searchResults.isSelected = true;
            }
            this.isSearching = false;
        },
        toggleVisibility() {
            if (!this.isVisible) {
                // We're about to show the dialog, reset the search term, and mode.
                this.filterVal = '';
                this.mode = 'main';
            }
            this.isVisible = !this.isVisible;
            if (this.isVisible) {
                Vue.nextTick(() => {
                    document.getElementById('supersonic-filter').focus();
                });
            }
        },
        visibilityHide() {
            this.isVisible = false;
        },
        mainSelectNext() {
            if (( this.selectedIdx + 1 ) < Object.keys(this.filteredActions).length) {
                this.selectedIdx++;
            }
            document.getElementById('action-' + this.selectedIdx).scrollIntoView({block: 'center'});
        },
        mainSelectPrevious() {
            if (this.selectedIdx > 0) {
                this.selectedIdx--;
            }
            document.getElementById('action-' + this.selectedIdx).scrollIntoView({block: 'center'});
        },
        leaveSearchAction() {
            this.mode = 'main';
        },
        selectNextSearchResult() {
            if (( this.selectedSearchIdx + 1 ) < this.searchResults.length) {
                this.selectedSearchIdx++;
            }
            document.getElementById('action-' + this.selectedSearchIdx).scrollIntoView({block: 'center'});
        },
        selectPreviousSearchResult() {
            if (this.selectedSearchIdx > 0) {
                this.selectedSearchIdx--;
            }
            document.getElementById('action-' + this.selectedSearchIdx).scrollIntoView({block: 'center'});
        },
        doAction(requestedAction) {
            var actions = null;
            if (this.mode === 'main') {
                actions = this.filteredActions[this.selectedIdx]?.actions;
            } else {
                actions = this.searchResults[this.selectedSearchIdx]?.actions;
            }
            if (typeof actions[requestedAction] === 'undefined') {
                return;
            }
            const action = actions[requestedAction];

            // Handle 'link' actions
            if (action.type === 'link') {
                if (typeof action.url === 'undefined') {
                    return;
                }
                window.location = action.url;
                return;
            }

            // Handle 'search' actions
            if (action.type === 'search') {
                // Reset the search view.
                this.searchVal = '';
                this.searchResults = [];
                this.isSearching = false;
                this.searchUrl = action.url;

                // Switch to search modal.
                this.mode = 'search';
                Vue.nextTick(() => {
                    document.getElementById('supersonic-filter').focus();
                });
                return;
            }
        },
        clickAction(idx) {
            if (this.mode === 'main') {
                this.selectedIdx = idx;
                this.doAction('primary');
            } else {
                this.selectedSearchIdx = idx;
                this.doAction('primary');
            }
        }
    }
}
</script>

<style scoped>
.modal-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 100;
}

.modal {
    min-height: 5rem;
    width: 50%;
    background-color: #191919;
    color: #f9f9f9;
    padding: 1rem 2rem 2rem 2rem;
    box-shadow: 1rem 1rem 1rem rgba(0, 0, 0, 0.4);
    max-height: 75vh;
    font-size: 15px;
}

.modal h2 {
    display: block;
    margin: auto;
    text-align: center;
    padding-bottom: 1.25rem;
}

.modal input {
    width: 100%;
    padding: 0.75rem;
    margin-bottom: 1.5rem;
    background-color: #333;
    outline-color: #666;
}

div.spinner-container, div.no-results-container {
    height: calc(75vh - 10.5rem);
    overflow: scroll;
    display: flex;
    align-items: center;
    justify-content: center;
}

.lds-ring {
    /* change color here */
    color: #db5556
}
.lds-ring,
.lds-ring div {
    box-sizing: border-box;
}
.lds-ring {
    display: inline-block;
    position: relative;
    width: 80px;
    height: 80px;
}
.lds-ring div {
    box-sizing: border-box;
    display: block;
    position: absolute;
    width: 64px;
    height: 64px;
    margin: 8px;
    border: 8px solid currentColor;
    border-radius: 50%;
    animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
    border-color: currentColor transparent transparent transparent;
}
.lds-ring div:nth-child(1) {
    animation-delay: -0.45s;
}
.lds-ring div:nth-child(2) {
    animation-delay: -0.3s;
}
.lds-ring div:nth-child(3) {
    animation-delay: -0.15s;
}
@keyframes lds-ring {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
