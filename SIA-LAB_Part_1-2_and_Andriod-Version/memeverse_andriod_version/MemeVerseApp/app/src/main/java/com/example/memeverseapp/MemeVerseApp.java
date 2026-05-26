package com.example.memeverseapp;

import android.app.Application;
import com.example.memeverseapp.network.RetrofitClient;

public class MemeVerseApp extends Application {
    @Override
    public void onCreate() {
        super.onCreate();
        RetrofitClient.init(this);
    }
}