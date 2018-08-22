<template>
    <div :id="'reply-'+id" class="card my-3">
        <div class="card-header">
            <div class="level">
                <h5 class="flex">
                    <a :href="'/profile/'+data.owner.name" v-text="data.owner.name"></a>
                    said <span v-text="ago"></span>...
                </h5>

                <div>
                    <favorite v-if="signedIn" :reply="data"></favorite>
                </div>

            </div>
        </div>
        <div class="card-body">
            <div v-if="editing">
                <form @submit="update">
                    <div class="form-group">
                        <textarea class="form-control" v-model="body" required></textarea>
                    </div>
                    <button class="btn btn-sm btn-primary">Update</button>
                    <button class="btn btn-sm btn-link" @click="editing = false" type="button">Cancel</button>
                </form>
            </div>
            <div v-else v-text="body"></div>
        </div>
        <div class="card-footer level" v-if="canUpdate">
            <button class="btn btn-sm mr-1" @click="editing = true">Edit</button>
            <button class="btn btn-danger btn-sm" @click="destroy">Delete</button>
        </div>
    </div>
</template>

<script>
    import Favorite from './Favorite';
    import moment from 'moment';

    export default {
        props: ['data'],

        components: {Favorite},
        data() {
            return {
                id: this.data.id,
                editing: false,
                body: this.data.body
            }
        },

        computed: {
            signedIn() {
                return window.App.signedIn;
            },
            canUpdate() {
                return this.authorize(user => this.data.user_id == user.id);
            },
            ago() {
                return moment(this.data.created_at + "Z").fromNow();
            },
        },

        methods: {
            update() {
                axios.patch('/replies/' + this.data.id, {
                    body: this.body
                })
                    .catch(error => {
                        flash(error.response.data, 'danger');
                    });

                this.editing = false;

                flash('Updated!');
            },

            destroy() {
                axios.delete('/replies/' + this.data.id);
                this.$emit('deleted', this.data.id);
            }
        }
    }
</script>