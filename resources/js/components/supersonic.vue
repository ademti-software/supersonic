<template>
    <div class="modal-container" v-show="isVisible">
        <div class="modal">
            <h2>{{ __('Supersonic') }}</h2>

            <!-- Main action pane -->
            <div v-if="mode === 'main'">
                <p>
                    <input type="text" id="supersonic-filter" v-model="mainView.filterVal">
                </p>
                <supersonic-action-list v-bind:actions="mainView.filteredActions"
                                        v-bind:selectedIdx="mainView.selectedIdx"
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
                    <input type="text" id="supersonic-filter" v-model="searchView.searchVal">
                </p>
                <supersonic-action-list
                    v-if="!searchView.isSearching && this.searchView.searchResults.length > 0"
                    v-bind:actions="searchView.searchResults"
                    v-bind:selectedIdx="searchView.selectedIdx"
                    @clickAction="clickAction">
                </supersonic-action-list>
                <div class="no-results-container" v-if="!searchView.isSearching && this.searchView.searchResults.length < 1">
                    <p v-if="!this.searchView.errorSearching && this.searchView.searchVal.length < 1">
                        {{ __('Type to search in ') }}{{ this.mainView.filteredActions[mainView.selectedIdx].path + ' » ' + this.mainView.filteredActions[mainView.selectedIdx].name }}
                    </p>
                    <p v-if="!this.searchView.errorSearching && this.searchView.searchVal.length > 0">
                        {{ __('No results') }}
                    </p>
                    <p v-if="this.searchView.errorSearching && this.searchView.customErrorMessage.length < 1">
                        {{ __('There was an error fetching results. Sorry.') }}
                    </p>
                    <p v-if="this.searchView.errorSearching && this.searchView.customErrorMessage.length > 0">
                        {{ searchView.customErrorMessage }}
                    </p>
                </div>
                <div class="spinner-container" v-if="searchView.isSearching">
                    <div>
                        <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
                        <p>{{ __('Searching...') }}</p>
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
        actions: {
            type: Object,
            required: true
        },
        isVisible: {
            type: Boolean,
            required: true
        },
        mode: {
            type: String,
            required: true
        },
    },
    data() {
        return {
            mainView: {
                filterVal: '',
                filteredActions: {},
                selectedIdx: 0,
            },
            searchView: {
                searchVal: '',
                customErrorMessage: '',
                errorSearching: false,
                searchResults: [],
                selectedIdx: 0,
                isSearching: false,
                searchUrl: '',
            }
        };
    },
    mounted() {
        // Set up handlers for filtering actions / search results.
        this.debouncedFilter = throttle(() => {
            this.filter();
        }, 100);
        this.debouncedSearch = debounce(() => {
            this.search();
        }, 300);
        // Show the initial list.
        this.filter();
    },
    beforeUnmount() {
        this.debouncedFilter.cancel();
    },
    watch: {
        'mainView.filterVal' () {
            this.debouncedFilter();
        },
        'searchView.searchVal' () {
            this.debouncedSearch();
        }
    },
    methods: {
        filter() {
            this.mainView.filteredActions = {};
            var lcFilterVal = this.mainView.filterVal.toLowerCase();
            var doneSelected = false;
            var matches = 0;
            this.mainView.selectedIdx = 0;
            for (var idx in this.actions) {
                if (this.actions[idx].searchName.includes(lcFilterVal)) {
                    this.mainView.filteredActions[matches] = Object.assign({
                        isSelected: !doneSelected,
                        ...this.actions[idx]
                    });
                    doneSelected = doneSelected || this.mainView.filteredActions[matches].isSelected;

                    matches++;
                }
            }
        },
        async search() {
            this.searchView.isSearching = true;
            this.searchView.errorSearching = false;
            this.searchView.searchResults = [];
            var lcSearchVal = this.searchView.searchVal.toLowerCase();

            const json = await fetch(this.searchView.searchUrl + '?' + new URLSearchParams({
                s: this.searchView.searchVal,
            })).then((response) => {
                this.searchView.selectedIdx = 0;
                if (!response.ok) {
                    this.searchView.customErrorMessage = '';
                    if (response.headers.has('x-supersonic-error')) {
                        this.searchView.customErrorMessage = response.headers.get('x-supersonic-error');
                    }
                    this.searchView.errorSearching = true;
                    return [];
                }

                return response.json();
            }).catch((error) => {
                this.searchView.errorSearching = true;
                return [];
            });

            this.searchView.searchResults = await json;

            if (this.searchView.searchResults.length > 0) {
                this.searchView.searchResults.isSelected = true;
            }
            this.searchView.isSearching = false;
        },
        toggleVisibility() {
            if (!this.isVisible) {
                // We're about to show the dialog, reset the search term, and mode.
                this.mainView.filterVal = '';
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
            if (( this.mainView.selectedIdx + 1 ) < Object.keys(this.mainView.filteredActions).length) {
                this.mainView.selectedIdx++;
            }
            document.getElementById('action-' + this.mainView.selectedIdx).scrollIntoView({block: 'center'});
        },
        mainSelectPrevious() {
            if (this.mainView.selectedIdx > 0) {
                this.mainView.selectedIdx--;
            }
            document.getElementById('action-' + this.mainView.selectedIdx).scrollIntoView({block: 'center'});
        },
        leaveSearchAction() {
            this.mode = 'main';
        },
        selectNextSearchResult() {
            if (( this.searchView.selectedIdx + 1 ) < this.searchView.searchResults.length) {
                this.searchView.selectedIdx++;
            }
            document.getElementById('action-' + this.searchView.selectedIdx).scrollIntoView({block: 'center'});
        },
        selectPreviousSearchResult() {
            if (this.searchView.selectedIdx > 0) {
                this.searchView.selectedIdx--;
            }
            document.getElementById('action-' + this.searchView.selectedIdx).scrollIntoView({block: 'center'});
        },
        doAction(requestedAction) {
            var actions = null;
            if (this.mode === 'main') {
                actions = this.mainView.filteredActions[this.mainView.selectedIdx]?.actions;
            } else {
                actions = this.searchView.searchResults[this.searchView.selectedIdx]?.actions;
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
                this.searchView.searchVal = '';
                this.searchView.searchResults = [];
                this.searchView.isSearching = false;
                this.searchView.searchUrl = action.url;

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
                this.mainView.selectedIdx = idx;
                this.doAction('primary');
            } else {
                this.searchView.selectedIdx = idx;
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
