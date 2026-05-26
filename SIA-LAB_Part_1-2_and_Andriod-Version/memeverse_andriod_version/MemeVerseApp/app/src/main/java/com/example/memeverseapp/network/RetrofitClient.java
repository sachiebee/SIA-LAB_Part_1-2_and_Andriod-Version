package com.example.memeverseapp.network;

import android.content.Context;
import android.util.Log;
import com.example.memeverseapp.utils.PreferencesManager;
import okhttp3.OkHttpClient;
import okhttp3.logging.HttpLoggingInterceptor;
import retrofit2.Retrofit;
import retrofit2.converter.gson.GsonConverterFactory;
import java.util.concurrent.TimeUnit;

public class RetrofitClient {
    private static final String TAG = "RetrofitClient";
    private static Retrofit retrofit = null;
    private static String currentBaseUrl = null;
    private static PreferencesManager prefManager = null;

    public static void init(Context context) {
        prefManager = new PreferencesManager(context);
        currentBaseUrl = prefManager.getBaseUrl();
        Log.d(TAG, "Initialized with base URL: " + currentBaseUrl);
    }

    public static void updateBaseUrl(Context context, String newBaseUrl) {
        if (prefManager == null) prefManager = new PreferencesManager(context);
        prefManager.setBaseUrl(newBaseUrl);
        currentBaseUrl = newBaseUrl;
        retrofit = null;
        Log.d(TAG, "Base URL updated to: " + currentBaseUrl);
    }

    public static Retrofit getClient() {
        if (retrofit == null) {
            if (prefManager == null) {
                throw new IllegalStateException("RetrofitClient.init() must be called before getClient()");
            }
            currentBaseUrl = prefManager.getBaseUrl();

            HttpLoggingInterceptor logging = new HttpLoggingInterceptor();
            logging.setLevel(HttpLoggingInterceptor.Level.BODY);

            OkHttpClient client = new OkHttpClient.Builder()
                    .addInterceptor(logging)
                    .connectTimeout(30, TimeUnit.SECONDS)
                    .readTimeout(30, TimeUnit.SECONDS)
                    .writeTimeout(30, TimeUnit.SECONDS)
                    .retryOnConnectionFailure(true)
                    .build();

            retrofit = new Retrofit.Builder()
                    .baseUrl(currentBaseUrl)
                    .addConverterFactory(GsonConverterFactory.create())
                    .client(client)
                    .build();

            Log.d(TAG, "Retrofit client created with URL: " + currentBaseUrl);
        }
        return retrofit;
    }

    public static String getFullUrl(String path) {
        if (path == null || path.isEmpty()) return null;

        // If it's already a full URL
        if (path.startsWith("http")) {
            return path;
        }

        // Handle avatar paths (just filename)
        if (!path.contains("/")) {
            path = "avatars/" + path;
        }

        String baseUrl = currentBaseUrl;
        if (!baseUrl.endsWith("/")) {
            baseUrl = baseUrl + "/";
        }

        if (path.startsWith("/")) {
            path = path.substring(1);
        }

        String fullUrl = baseUrl + path;
        Log.d(TAG, "Full URL generated: " + fullUrl);
        return fullUrl;
    }
}