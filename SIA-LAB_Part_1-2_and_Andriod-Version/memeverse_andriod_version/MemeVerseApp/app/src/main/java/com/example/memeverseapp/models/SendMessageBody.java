package com.example.memeverseapp.models;

public class SendMessageBody {
    private int receiver_id;
    private String message;

    public SendMessageBody(int receiver_id, String message) {
        this.receiver_id = receiver_id;
        this.message = message;
    }

    public int getReceiver_id() { return receiver_id; }
    public String getMessage() { return message; }
}