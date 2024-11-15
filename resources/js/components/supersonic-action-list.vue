<template>
    <div class="list-container">
        <ul>
            <li v-for="(action, index) in actions"
                :id="'action-' + index"
                :class="'indent-' + action.depth + ' ' +
                ((Number(index) === selectedIdx) ? 'action-selected' : '')"
                @click="$emit('clickAction', index)">
                <span class="action-icon" v-html="action.svgIcon"></span>
                {{ action.name }}
                <span class="action-path">{{ action.path }}</span>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    props: {
        actions: { type: Object, required: true },
        selectedIdx: { type: Number, required: true }
    },
    data() {
        return {
        };
    }
}
</script>

<style scoped>
    div.list-container {
        height: calc(75vh - 10.5rem);
        overflow: scroll;
    }

    ul div.list-container {
        height: auto;
        overflow: auto;
    }
    ul {
        border-top: 1px solid #333;
        margin-top: 0.5rem;
    }

    li {
        padding: 0.5rem;
        border-bottom: 1px solid #333;
        display: block;
        width: 100%;
        cursor: pointer;
    }
    li:last-child {
        border-bottom: none;
    }
    li span.action-path {
        color: #999;
        font-size: 70%;
        float: right;
        margin-top: 0.35rem;
    }
    li.action-selected span.action-path {
        color: #fff;
    }
    li span.action-icon {
        display: inline-block;
        width: 1.25rem;
        height: 1.25rem;
        margin-top: 0.25rem;
        margin-right: 0.8rem;
        vertical-align: text-bottom;
    }
    li.action-selected {
        color: #fff;
        font-weight: bold;
        background-color: #db5556;
    }
    @media (max-width: 768px) {
        li span {
            display: none;
        }
    }
    .indent-1 {
        text-indent: 1rem;
    }
</style>
