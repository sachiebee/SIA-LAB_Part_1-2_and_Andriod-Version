package com.example.memeverseapp.models;

public class ReportBody {
    private String type;
    private int id;
    private String reason;

    public ReportBody(String type, int id, String reason) {
        this.type = type;
        this.id = id;
        this.reason = reason;
    }

    public String getType() { return type; }
    public int getId() { return id; }
    public String getReason() { return reason; }
}