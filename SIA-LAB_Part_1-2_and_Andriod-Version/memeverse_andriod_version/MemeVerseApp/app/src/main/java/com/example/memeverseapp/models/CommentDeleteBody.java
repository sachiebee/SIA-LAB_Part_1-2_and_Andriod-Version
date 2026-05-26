package com.example.memeverseapp.models;

public class CommentDeleteBody {
    private int comment_id;

    public CommentDeleteBody(int comment_id) {
        this.comment_id = comment_id;
    }

    public int getComment_id() {
        return comment_id;
    }
}