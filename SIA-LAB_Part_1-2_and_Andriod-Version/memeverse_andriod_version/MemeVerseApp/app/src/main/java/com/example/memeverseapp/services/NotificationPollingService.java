package com.example.memeverseapp.services;

import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.app.Service;
import android.content.Intent;
import android.os.Build;
import android.os.Handler;
import android.os.IBinder;
import android.util.Log;
import androidx.core.app.NotificationCompat;
import androidx.lifecycle.LiveData;
import androidx.lifecycle.MutableLiveData;
import com.example.memeverseapp.R;
import com.example.memeverseapp.models.UnreadCountResponse;
import com.example.memeverseapp.network.ApiService;
import com.example.memeverseapp.network.RetrofitClient;
import com.example.memeverseapp.ui.MainActivity;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class NotificationPollingService extends Service {
    private static final String TAG = "PollingService";
    private static final long POLLING_INTERVAL = 5000;
    private Handler handler = new Handler();
    private Runnable pollingRunnable;
    private ApiService apiService;

    private static final MutableLiveData<Integer> unreadNotificationsCount = new MutableLiveData<>(0);
    private static final MutableLiveData<Integer> unreadMessagesCount = new MutableLiveData<>(0);

    public static LiveData<Integer> getUnreadNotificationsCount() { return unreadNotificationsCount; }
    public static LiveData<Integer> getUnreadMessagesCount() { return unreadMessagesCount; }

    @Override
    public void onCreate() {
        super.onCreate();
        apiService = RetrofitClient.getClient().create(ApiService.class);
        startForeground();
    }

    private void startForeground() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            NotificationChannel channel = new NotificationChannel(
                    "polling_channel", "Notification Polling", NotificationManager.IMPORTANCE_LOW);
            NotificationManager manager = getSystemService(NotificationManager.class);
            if (manager != null) manager.createNotificationChannel(channel);
        }

        Intent intent = new Intent(this, MainActivity.class);
        PendingIntent pendingIntent = PendingIntent.getActivity(this, 0, intent,
                PendingIntent.FLAG_UPDATE_CURRENT | PendingIntent.FLAG_IMMUTABLE);

        NotificationCompat.Builder builder = new NotificationCompat.Builder(this, "polling_channel")
                .setContentTitle("MemeVerse")
                .setContentText("Checking for new notifications...")
                .setSmallIcon(R.drawable.ic_notifications)
                .setContentIntent(pendingIntent)
                .setPriority(NotificationCompat.PRIORITY_LOW);

        startForeground(1001, builder.build());
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        startPolling();
        return START_STICKY;
    }

    private void startPolling() {
        pollingRunnable = new Runnable() {
            @Override
            public void run() {
                fetchUnreadCounts();
                handler.postDelayed(this, POLLING_INTERVAL);
            }
        };
        handler.post(pollingRunnable);
    }

    private void fetchUnreadCounts() {
        apiService.getUnreadNotifications().enqueue(new Callback<UnreadCountResponse>() {
            @Override
            public void onResponse(Call<UnreadCountResponse> call, Response<UnreadCountResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    unreadNotificationsCount.postValue(response.body().getCount());
                }
            }
            @Override
            public void onFailure(Call<UnreadCountResponse> call, Throwable t) {}
        });

        apiService.getUnreadMessages().enqueue(new Callback<UnreadCountResponse>() {
            @Override
            public void onResponse(Call<UnreadCountResponse> call, Response<UnreadCountResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    unreadMessagesCount.postValue(response.body().getCount());
                }
            }
            @Override
            public void onFailure(Call<UnreadCountResponse> call, Throwable t) {}
        });
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        if (handler != null && pollingRunnable != null) handler.removeCallbacks(pollingRunnable);
    }

    @Override
    public IBinder onBind(Intent intent) { return null; }
}